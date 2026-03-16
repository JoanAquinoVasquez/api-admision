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
            $token = $request->header('X-Chatbot-Token');
            if ($token !== env('CHATBOT_TOKEN')) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 401);
            }

            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $response = $this->chatbotService->chat($validated['message']);

            return $this->successResponse(['reply' => $response]);
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
