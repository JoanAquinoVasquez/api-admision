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
            ]);

            $response = $this->chatbotService->chat($validated['message']);

            return $this->successResponse(['reply' => $response]);
        }, 'Error al procesar el mensaje');
    }
}
