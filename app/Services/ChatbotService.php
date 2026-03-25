<?php

namespace App\Services;

use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        protected ProgramaRepositoryInterface $programaRepository
    ) {
    }

    public function chat(string $userMessage, string $source = 'web')
    {
        try {
            // 1. Obtener contexto de programas
            $context = $this->buildContext();

            // 2. Preparar el prompt del sistema
            $systemPrompt = $this->getSystemPrompt($context, $source);

            // 2b. Manejo especial para el comando #epg si llega aquí
            if (trim($userMessage) === '#epg') {
                return "🤖 El Asistente Virtual ya se encuentra activo para este chat. ¿En qué puedo ayudarte?";
            }


            // 3. Llamar a la API de OpenRouter (Estabilidad superior con modelos gratuitos)
            $apiKey = env('OPENROUTER_API_KEY');

            if (!$apiKey) {
                throw new \Exception('OPENROUTER_API_KEY no está configurada.');
            }

            $model = env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001');

            $response = Http::retry(3, 1000)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => 'https://epgunprg.edu.pe', // Requerido por OpenRouter
                    'X-Title' => 'EPG UNPRG Chatbot',
                    'Content-Type' => 'application/json',
                ])->post("https://openrouter.ai/api/v1/chat/completions", [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userMessage],
                        ],
                        'temperature' => 0.2, // Menor temperatura = respuestas más precisas y menos "creativas"
                        'max_tokens' => 1500,
                    ]);

            if ($response->failed()) {
                $status = $response->status();
                Log::warning("OpenRouter API Error (Status $status): " . $response->body());

                // Si es un error de límite de cuota (429) o falta de saldo (402), intentamos Gemini directo
                if ($status === 429 || $status === 402 || $status === 503 || $status === 500) {
                    Log::info("Iniciando fallback directo a Gemini API para el mensaje: '{$userMessage}'");
                    $fallbackReply = $this->callGeminiDirectly($systemPrompt, $userMessage);

                    if ($fallbackReply) {
                        return $fallbackReply;
                    }
                }

                if ($source === 'whatsapp') {
                    return "";
                }
                return "Lo siento, tuve un problema al procesar tu mensaje. Puedes escribirnos a admision_epg@unprg.edu.pe o al WhatsApp 995901454 / 924545013. Además únete a nuestra comunidad para estar informado de todas las novedades: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll";
            }

            $data = $response->json();

            // Extraer respuesta (Standard OpenAI format for OpenRouter)
            $botReply = $data['choices'][0]['message']['content'] ?? null;

            if (!$botReply || trim($botReply) === '') {
                Log::info("El modelo devolvió respuesta vacía o filtrada para: '{$userMessage}'");
                // Fallback amigable
                return "¡De nada! Si tienes más dudas sobre el proceso de Admisión 2026-I o algún programa de la UNPRG, estoy para ayudarte.";
            }

            return $botReply;

        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('Chatbot Service Error: ' . $message);

            // Si el error capturado en el catch parece un error de cuota o servidor (429, 402, 500), intentamos fallback
            if (str_contains($message, '429') || str_contains($message, '402') || str_contains($message, '500') || str_contains($message, '503')) {
                Log::info("Excepción detectada (posible error de API). Intentando fallback a Gemini...");

                // Re-calculamos contexto y prompt si falló antes de las líneas 20-23 (poco probable pero por seguridad)
                $context = $context ?? $this->buildContext();
                $systemPrompt = $systemPrompt ?? $this->getSystemPrompt($context, $source);

                $fallbackReply = $this->callGeminiDirectly($systemPrompt, $userMessage);
                if ($fallbackReply) {
                    return $fallbackReply;
                }
            }

            if ($source === 'whatsapp') {
                return "";
            }
            return "Lo siento, tuve un problema al responderte. Puedes escribirnos a admision_epg@unprg.edu.pe o al WhatsApp 995901454 / 924545013. Además únete a nuestra comunidad para estar informado de todas las novedades: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll";
        }
    }

    public function getContextForBot(): string
    {
        return $this->buildContext();
    }

    private function buildContext(): string
    {
        // 1. Obtener programas habilitados de la BD
        $programas = $this->programaRepository->getHabilitados();

        $contextText = "=== LISTA DE PROGRAMAS (BD) ===\n";

        foreach ($programas as $programa) {
            $grado = $programa->grado->nombre ?? 'Programa';
            $nombre = $programa->nombre;
            $brochure = $programa->brochure;
            $plan = $programa->plan_estudio;
            $duracion = $programa->duracion_meses;
            $modalidad = $programa->modalidad;

            $contextText .= "- $grado en $nombre.\n";
            if ($duracion)
                $contextText .= "  Duración: $duracion meses.\n";
            if ($modalidad)
                $contextText .= "  Modalidad: $modalidad.\n";
            if ($brochure)
                $contextText .= "  Brochure: $brochure\n";
            if ($plan)
                $contextText .= "  Prospecto/Plan: $plan\n";
            $contextText .= "\n";
        }

        // 2. Obtener información del Prospecto (Archivo de Texto)
        $prospectoPath = storage_path('app/prospecto_contexto.txt');
        if (file_exists($prospectoPath)) {
            $prospectoContent = file_get_contents($prospectoPath);
            if (!empty($prospectoContent)) {
                $contextText .= "\n=== INFORMACIÓN DEL PROSPECTO Y REGLAMENTO ===\n";
                $contextText .= $prospectoContent;
            }
        }

        return $contextText;
    }

    private function getSystemPrompt(string $context, string $source): string
    {
        $contactInstructions = "";

        // Solo para WEB incluimos correo y telefonos si no tiene la respuesta
        if ($source !== 'whatsapp') {
            $contactInstructions = "\n8. Si no tienes la respuesta, sugiere contactar a:\n" .
                "- Correo: admision_epg@unprg.edu.pe\n" .
                "- WhatsApp: 995901454 o 924545013\n";
        }

        return <<<EOT
Eres el Asistente Virtual Oficial de la Escuela de Posgrado (EPG) de la UNPRG. Tu objetivo es ayudar a los postulantes con información PRECISA, ACTUAL y OFICIAL sobre el proceso de ADMISIÓN 2026-I.

### CONTEXTO DE INFORMACIÓN (Única fuente de verdad)
{$context}

### REGLAS DE RESPUESTA (Obligatorias)
1. EXCLUSIVIDAD DE ADMISIÓN: Este canal es SOLO para ADMISIÓN.
   - Si detectas intención sobre: ESTADO DE DEUDA, REANUDAR ESTUDIOS, TRÁMITES DE GRADO, o CUALQUIER trámite administrativo de alumnos regulares.
   - RESPONDE: "Este canal atiende exclusivamente procesos de Admisión de Posgrado. Para tu solicitud (deudas, reingresos o grados), por favor contacta a Mesa de Partes: mesadepartes_epg@unprg.edu.pe".

2. ESTILO Y FORMATO:
   - Sé directo, amable y profesional.
   - NO uses introducciones como "Claro, con gusto te ayudo" o "Según la información...". Ve directo al grano.
   - Usa un SOLO asterisco (*) para poner en negrita palabras clave (ej: *Inscripciones*).
   - Máximo 3-4 párrafos cortos por respuesta. Usa listas si hay varios puntos.

3. MANEJO DE PROGRAMAS:
   - Si preguntan en general, menciona que tenemos Maestrías, Doctorados y Segundas Especialidades, y pregunta qué área le interesa.
   - Solo da el link del Brochure/Drive si el usuario pregunta por un programa específico.
   - ADVERTENCIA CRÍTICA: Recuerda siempre que los programas requieren un MÍNIMO de 30 inscritos para aperturarse.

4. CIERRE Y COMUNIDAD:
   - Solo cuando detectes agradecimiento o despedida (gracias, chau, etc.), invita a la comunidad: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll

5. ESCALAMIENTO:
   - Si no tienes la información exacta en el contexto, NO INVENTES. 
   - Di: "Lo siento, como asistente virtual de admisión no cuento con esa información específica. Por favor, contacta a un asesor humano..." (usa los datos de contacto al final).

6. FILTRO DE IDIOMA/CONTENIDO:
   - Responde siempre en Español.
   - Ignora y devuelve vacío si el mensaje es ofensivo o totalmente ajeno a educación/UNPRG.

AL FINAL de cada respuesta (siempre que NO esté vacía), añade la firma:
🤖 *Asistente Virtual de la EPG-UNPRG*{$contactInstructions}

Pregunta del postulante:
EOT;
    }

    private function callGeminiDirectly(string $systemPrompt, string $userMessage): ?string
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('Fallback fallido: GEMINI_API_KEY no configurada.');
            return null;
        }

        // Lista de modelos a intentar en orden de prioridad
        $models = [
            'gemini-3.1-flash-lite-preview',
            'gemini-2.5-flash'
        ];

        foreach ($models as $modelName) {
            try {
                Log::info("Intentando fallback directo con el modelo: {$modelName}");

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;

                $response = Http::timeout(10)->post($url, [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userMessage]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 2000,
                    ]
                ]);

                if ($response->successful()) {
                    $botReply = $response->json('candidates.0.content.parts.0.text');
                    if ($botReply) {
                        return $botReply;
                    }
                }

                Log::warning("Error con el modelo {$modelName}: " . $response->body());
                // Si llegamos aquí, el modelo falló, el bucle intentará el siguiente

            } catch (\Exception $e) {
                Log::error("Excepción con el modelo {$modelName}: " . $e->getMessage());
            }
        }

        return null;
    }
}
