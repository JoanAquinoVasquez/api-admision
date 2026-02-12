<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\ResultadosService;
use Illuminate\Http\Request;

class ResultadosController extends BaseController
{
    public function __construct(
        protected ResultadosService $resultadosService,
        protected ReportService $reportService
    ) {
    }

    /**
     * Método para obtener todos los registros
     */
    public function index()
    {
        try {
            $ranking = $this->resultadosService->getRankingMerito();
            return $this->successResponse($ranking);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los postulantes con mérito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportIngresantesPrograma()
    {
        return $this->reportService->generateIngresantesTopPDF();
    }

    public function ingresantesPorPrograma()
    {
        try {
            $resultado = $this->resultadosService->getIngresantesPorPrograma();
            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function resumenPorEdad()
    {
        try {
            $resumen = $this->resultadosService->getResumenPorEdad();
            return $this->successResponse($resumen);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resumenGeneral()
    {
        try {
            $resumen = $this->resultadosService->getResumenGeneral();
            return $this->successResponse($resumen);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function histogramaNotas(Request $request)
    {
        try {
            $intervalos = $request->input('intervalos');
            $resultado = $this->resultadosService->getHistogramaNotas($intervalos);

            if (empty($resultado)) {
                return response()->json([
                    'message' => 'No hay notas disponibles para construir el histograma.'
                ]);
            }

            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
