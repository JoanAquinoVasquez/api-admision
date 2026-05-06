<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends BaseController
{
    public function __construct(
        protected ChatbotService $chatbotService
    ) {
    }

    public function chat(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
                'source' => 'nullable|string',
                'user_number' => 'nullable|string'
            ]);

            $source = $validated['source'] ?? 'web';

            // Solo validamos token si viene de WhatsApp
            if ($source === 'whatsapp') {
                $token = $request->header('X-Chatbot-Token');
                if ($token !== env('CHATBOT_TOKEN')) {
                    return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
                }
            }

            $response = $this->chatbotService->chat($validated['message'], $source);

            // Log de la conversación en BD
            \App\Models\ChatbotLog::create([
                'message_user' => $validated['message'],
                'message_bot' => $response,
                'source' => $source,
                'ip_address' => $request->ip(),
                'user_identifier' => $validated['user_number'] ?? null
            ]);

            $handover = false;
            if ($response && str_contains($response, '[HANDOVER]')) {
                $handover = true;
                $response = str_replace('[HANDOVER]', '', $response);
                $response = trim($response);
            }

            return $this->successResponse([
                'reply' => $response,
                'handover' => $handover
            ]);
        }, 'Error al procesar el mensaje');
    }

    public function getContext(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $token = $request->header('X-Chatbot-Token');
            if ($token !== env('CHATBOT_TOKEN')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
            }

            // Usamos la reflexión o un método público para obtener el contexto si es necesario, 
            // pero ChatbotService ya tiene buildContext. Como es private, lo ideal sería tener un método que lo exponga.
            // Por simplicidad y sin tocar mucho el Service, podemos llamar a un método que lo devuelva.

            // Vamos a verificar si buildContext es accesible o si necesitamos un wrapper.
            // El usuario ya modificó ChatbotService, veamos si puedo añadir un método público ahí.
            $context = $this->chatbotService->getContextForBot();

            return $this->successResponse(['context' => $context]);
        }, 'Error al obtener el contexto');
    }
}
