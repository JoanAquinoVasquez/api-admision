<?php

namespace App\Http\Controllers;

use App\Services\NotaService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class NotaController extends BaseController
{
    public function __construct(
        protected NotaService $notaService,
        protected ReportService $reportService
    ) {
    }

    /**
     * Listar los postulantes aptos para entrevista por programa en PDF
     */
    public function postulantesAptos($idPrograma)
    {
        return $this->handleRequest(function () use ($idPrograma) {
            $pdf = $this->reportService->generatePostulantesAptosPDF($idPrograma);
            return $pdf;
        }, 'Error al generar el PDF de postulantes aptos');
    }

    /**
     * Listar los postulantes aptos para entrevista por múltiples programas en PDF
     */
    public function postulantesAptosMultiple()
    {
        return $this->handleRequest(function () {
            $pdf = $this->reportService->generatePostulantesAptosMultiplePDF();
            return $pdf;
        }, 'Error al generar el PDF de postulantes aptos');
    }

    /**
     * Importar notas del examen de admisión desde Excel
     */
    public function storeExamenAdmision(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:2048',
            ]);

            $resultado = $this->notaService->importExamGrades($request->file('file'));

            if ($resultado['success']) {
                $this->logActivity('Notas de examen importadas', null, [
                    'archivo' => $request->file('file')->getClientOriginalName(),
                ]);
            }

            return $this->successResponse($resultado, $resultado['message']);
        }, 'Error al importar las notas del examen');
    }

    /**
     * Guardar nota de entrevista
     */
    public function guardarNotaEntrevista(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'inscripcion_id' => 'required|exists:inscripcions,id',
                'nota_entrevista' => 'required|numeric|min:0|max:20',
            ]);

            $resultado = $this->notaService->saveInterviewGrade(
                $validated['inscripcion_id'],
                $validated['nota_entrevista']
            );

            if ($resultado['success']) {
                $this->logActivity('Nota de entrevista guardada', null, [
                    'inscripcion_id' => $validated['inscripcion_id'],
                    'nota' => $validated['nota_entrevista'],
                ]);
            }

            return $this->successResponse($resultado, $resultado['message']);
        }, 'Error al guardar la nota de entrevista');
    }

    /**
     * Generar reporte final de notas con mérito
     */
    public function reportFinalNotas()
    {
        $programas = $this->notaService->calculateFinalGrades();

        $pdf = Pdf::loadView('notas.notas-finales', ['programas' => $programas]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("Ingresantes_" . now()->format('d-m-Y_His') . ".pdf");
    }

    /**
     * Resumen de evaluación por programa
     */
    public function resumenEvaluacion()
    {
        return $this->handleRequest(function () {
            $resumen = $this->notaService->getEvaluationSummary();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de evaluación');
    }

    public function resumenNotasDiarias()
    {
        return $this->handleRequest(function () {
            $resumen = $this->notaService->getNotasDiariasSummary();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de evaluación');
    }
}
