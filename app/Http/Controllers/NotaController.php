<?php

namespace App\Http\Controllers;

use App\Services\NotaService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

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
                'notas_examen' => 'required|mimes:xlsx,xls|max:4096',
            ]);

            $file = $request->file('notas_examen');
            $resultado = $this->notaService->importExamGrades($file);

            if ($resultado['success']) {
                $this->logActivity('Notas de examen importadas', null, [
                    'archivo' => $file->getClientOriginalName(),
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
            $inscripcionId = $request->input('inscripcion_id');
            $inscripcion = \App\Models\Inscripcion::with(['programa.grado', 'postulante'])->find($inscripcionId);

            if (!$inscripcion) {
                return $this->errorResponse('Inscripción no encontrada', 404);
            }

            $gradoId = $inscripcion->programa->grado_id;
            $maxNota = ($gradoId == 3) ? 30 : 40; // 3: SEGUNDA ESPECIALIDAD PROFESIONAL

            $validated = $request->validate([
                'inscripcion_id' => 'required|exists:inscripcions,id',
                'nota_entrevista' => "required|numeric|min:0|max:{$maxNota}",
            ]);

            $resultado = $this->notaService->saveInterviewGrade(
                $validated['inscripcion_id'],
                $validated['nota_entrevista']
            );

            if ($resultado['success']) {
                $usuario = Auth::user();
                $postulante = $inscripcion->postulante;
                $nombrePostulante = "{$postulante->ap_paterno} {$postulante->ap_materno}, {$postulante->nombres}";
                $nombreUsuario = $usuario->nombres; // Para admins el campo suele ser solo nombres

                $this->logActivity('Nota de entrevista guardada', null, [
                    'usuario' => $nombreUsuario,
                    'subject' => [
                        'nombres' => $postulante->nombres,
                        'ap_paterno' => $postulante->ap_paterno,
                        'ap_materno' => $postulante->ap_materno,
                        'tipo_doc' => $postulante->tipo_doc,
                        'num_iden' => $postulante->num_iden,
                    ],
                    'programa' => $inscripcion->programa->nombre,
                    'grado' => $inscripcion->programa->grado->nombre,
                    'nota' => $validated['nota_entrevista'],
                    'mensaje' => "El usuario {$nombreUsuario} registró la nota de entrevista de {$validated['nota_entrevista']} al postulante {$nombrePostulante}"
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
