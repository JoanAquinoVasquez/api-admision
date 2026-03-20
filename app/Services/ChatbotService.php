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


            // 3. Llamar a la API de Gemini
            $apiKey = config('services.gemini.api_key');
            // Asegurarse de tener esto en config/services.php o usar env directamente si se prefiere rapido, 
            // pero lo correcto es config. Usaremos env por ahora para no modificar config si no es necesario, o checkearemos.
            // Mejor usar env('GEMINI_API_KEY') directo si no estoy seguro del config.
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception('GEMINI_API_KEY no está configurada.');
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key={$apiKey}", [
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
                Log::error('Gemini API Error: ' . $response->body());
                if ($source === 'whatsapp') {
                    return "";
                }
                return "Lo siento, tuve un problema al procesar tu mensaje. Puedes escribirnos a admision_epg@unprg.edu.pe o al WhatsApp 995901454 / 924545013. Además únete a nuestra comunidad para estar informado de todas las novedades: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll";
            }

            $data = $response->json();

            // Extraer respuesta
            $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$botReply) {
                // Si Gemini bloqueó la respuesta por filtros, damos una respuesta amable por defecto
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
1. NO SALUDES ni uses introducciones. Ve DIRECTO a la información.
2. Si el usuario pregunta cosas generales, NO mandes toda la lista. Responde de forma breve y pregunta por el programa específico.
3. Solo proporciona el link de Drive si el usuario especifica un programa.
4. FORMATO: Usa un SOLO asterisco (*) para negritas.
5. COSTOS ESPECÍFICOS: Usa la "Duración" (en meses) de cada programa para determinar su costo según las reglas del Prospecto (18 meses = 3 semestres, 24 meses = 4 semestres). No digas "depende de la duración" si ya tienes la duración específica en el DATABASE CONTEXT.
6. COMUNIDAD: Solo cuando el usuario te agradezca o se esté despidiendo (ej: "gracias", "chau", "listo"), invítalo a unirse a nuestra comunidad oficial: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll
7. SILENCIO (CRÍTICO): Si te preguntan algo que NO está en el DATABASE CONTEXT, no sabes la respuesta, o si el usuario pide HABLAR CON UN ASESOR/PERSONA REAL/HUMANO:
   - Si la fuente es WhatsApp (source: whatsapp), responde ÚNICAMENTE con una cadena vacía (sin firma, sin nada).
   - Si la fuente es Web, sugiere contactar a la oficina de admisión.
8. AL FINAL de cada respuesta (siempre que NO esté vacía), añade la firma: 🤖 _Asistente Virtual de la EPG-UNPRG_{$contactInstructions}

Usuario pregunta:
EOT;
    }

}
