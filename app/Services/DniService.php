<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DniService
{
    // Método para consultar la API de RENIEC
    // Método para consultar la API de RENIEC
    public function getDniData($dni)
    {
        $token = env('TOKEN_DECOLECTA_API'); // Tu token

        // Realizar la solicitud a la API de RENIEC
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get('https://api.decolecta.com/v1/reniec/dni', [
                        'numero' => $dni,
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                // 🔹 Mapear de Decolecta → tu formato anterior
                return [
                    'success' => true,
                    'data' => [
                        "nombres" => $data["first_name"] ?? null,
                        "apellidoPaterno" => $data["first_last_name"] ?? null,
                        "apellidoMaterno" => $data["second_last_name"] ?? null,
                        "dni" => $data["document_number"] ?? null,
                    ]
                ];
            }

            return ['success' => false];
        } catch (\Exception $e) {
            // Manejo de excepciones
            return ['success' => false];
        }
    }


}
