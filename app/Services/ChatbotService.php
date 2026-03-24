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


            // 3. Llamar a la API de Gemini
            $apiKey = config('services.gemini.api_key');
            // Asegurarse de tener esto en config/services.php o usar env directamente si se prefiere rapido, 
            // pero lo correcto es config. Usaremos env por ahora para no modificar config si no es necesario, o checkearemos.
            // Mejor usar env('GEMINI_API_KEY') directo si no estoy seguro del config.
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception('GEMINI_API_KEY no está configurada.');
            }

            $response = Http::retry(3, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $systemPrompt . "\n\nUsuario: " . $userMessage]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => 2048,
                        ],
                        // Añadimos esto para evitar bloqueos por agradecimientos o frases cortas
                        'safetySettings' => [
                            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
                        ]
                    ]);

            if ($response->failed()) {
                Log::warning('Gemini API Error (failed): ' . $response->body());
                if ($source === 'whatsapp') {
                    return "";
                }
                return "Lo siento, tuve un problema al procesar tu mensaje. Puedes escribirnos a admision_epg@unprg.edu.pe o al WhatsApp 995901454 / 924545013. Además únete a nuestra comunidad para estar informado de todas las novedades: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll";
            }

            $data = $response->json();

            // Extraer respuesta
            $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$botReply || trim($botReply) === '') {
                Log::info("Gemini devolvió respuesta vacía o filtrada para: '{$userMessage}'");
                // Si Gemini bloqueó la respuesta por filtros o devolvió vacío
                return "¡De nada! Si tienes más dudas sobre el proceso de Admisión 2026-I o algún programa de la UNPRG, estoy para ayudarte.";
            }

            return $botReply;

        } catch (\Exception $e) {
            Log::error('Chatbot Service Error: ' . $e->getMessage());
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
Eres el Asistente Virtual Oficial de la Escuela de Posgrado (EPG) de la UNPRG. Solo respondes basándote en la información ACTUAL y OFICIAL proporcionada.

### DATABASE CONTEXT
{$context}

### REGLAS DE ORO
1. EXCLUSIVIDAD (CRÍTICO): Este canal es ÚNICAMENTE para ADMISIÓN e INSCRIPCIONES.
   - Si preguntan sobre ESTADO DE DEUDA, REANUDAR MAESTRÍA/ESTUDIOS, TRÁMITES DE SUSTENTACIÓN/GRADO o CUALQUER OTRO TRÁMITE ADMINISTRATIVO:
     * Responde que: "Este canal atiende exclusivamente procesos de Admisión. Para tu solicitud (deudas, expedientes, reingresos o grados), favor de coordinar con Mesa de Partes vía correo: mesadepartes_epg@unprg.edu.pe".
2. NO SALUDES ni uses introducciones. Ve DIRECTO a la información.
3. Si el usuario pregunta cosas generales, NO mandes toda la lista. Responde de forma breve y pregunta por el programa específico.
4. Solo proporciona el link de Drive si el usuario especifica un programa.
5. FORMATO: Usa un SOLO asterisco (*) para negritas.
6. COMUNIDAD: Solo cuando el usuario te agradezca o se esté despidiendo (ej: "gracias", "chau", "listo"), invítalo a unirse a nuestra comunidad oficial: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll
7. SI NO SABES LA RESPUESTA o si el usuario pide HABLAR CON UN ASESOR/PERSONA REAL/HUMANO:
   - Responde de forma amable indicando que como asistente virtual no tienes esa información o que el usuario debe contactar a un asesor humano a los números brindados al final.
   - NO inventes información.
   - Solo mantente en SILENCIO (cadena vacía) si te preguntan algo COMPLETAMENTE ajeno a la UNPRG o la educación (ej: recetas, deportes internacionales, etc).
8. AL FINAL de cada respuesta (siempre que NO esté vacía), añade la firma: 🤖 _Asistente Virtual de la EPG-UNPRG_{$contactInstructions}

Usuario pregunta:
EOT;
    }

}
