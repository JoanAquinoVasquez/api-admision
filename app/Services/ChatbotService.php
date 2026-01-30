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

    public function chat(string $userMessage)
    {
        try {
            // 1. Obtener contexto de programas
            $context = $this->buildContext();

            // 2. Preparar el prompt del sistema
            $systemPrompt = $this->getSystemPrompt($context);

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
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
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
                        ]
                    ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return "Lo siento, tuve un problema al procesar tu consulta. Por favor intenta más tarde.";
            }

            $data = $response->json();

            // Extraer respuesta
            $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "No pude generar una respuesta.";
            Log::info($botReply);
            return $botReply;

        } catch (\Exception $e) {
            Log::error('Chatbot Service Error: ' . $e->getMessage());
            return "Ocurrió un error interno. Por favor contacta a soporte.";
        }
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

            $contextText .= "- $grado en $nombre.\n";
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

    private function getSystemPrompt(string $context): string
    {
        return <<<EOT
Eres el Asistente Virtual de Admisión de la Escuela de Posgrado (EPG) de la Universidad Nacional Pedro Ruiz Gallo (UNPRG).
Tu objetivo es ayudar a los postulantes brindando información precisa sobre el proceso de admisión, los programas de maestrías, doctorados y segundas especialidades.

Aquí tienes la información ACTUALA y OFICIAL de los programas:
{$context}

Instrucciones:
1. NO SALUDES ni te presentes en cada respuesta. Ve DIRECTO a la información solicitada.
2. Responde de manera concisa y breve.
3. Si te preguntan por un programa específico, busca en la lista y proporciona el enlace al brochure o prospecto si está disponible.
4. Si la información no está en la lista, indica que no tienes esa información específica y sugiere contactar a la oficina de admisión.
5. Intenta persuadir al usuario resaltando los beneficios de estudiar un posgrado.
6. NO inventes enlaces ni información que no esté en el contexto provisto.
7. El proceso de admisión es 2026-I.
8. Si no tienes la respuesta, sugiere contactar a:
   - Correo: admision_epg@unprg.edu.pe
   - WhatsApp: 995901454 o 924545013
9. Recomienda unirse a nuestra comunidad de WhatsApp para novedades: https://chat.whatsapp.com/FQjt9M0b5hn56cQ8NrYlll

Usuario pregunta:
EOT;
    }
}
