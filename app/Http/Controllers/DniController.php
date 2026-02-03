<?php

namespace App\Http\Controllers;

use App\Services\DniService;
use Illuminate\Http\Request;

class DniController extends BaseController
{
    public function __construct(
        protected DniService $dniService
    ) {
    }

    /**
     * Consult RENIEC API for DNI data
     */
    public function consultarDni(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'dni' => 'required|digits:8',
            ]);

            $payload = $this->dniService->getDniData($validated['dni']);

            $this->logActivity('Consulta DNI realizada', null, [
                'dni' => $validated['dni'],
            ]);

            return $this->successResponse($payload);
        }, 'Error al consultar el DNI');
    }
}
