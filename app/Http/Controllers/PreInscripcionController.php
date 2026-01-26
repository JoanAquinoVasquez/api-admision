<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreInscripcionRequest;
use App\Http\Requests\UpdatePreInscripcionRequest;
use App\Services\PreInscripcionService;
use Illuminate\Http\Request;

class PreInscripcionController extends BaseController
{
    public function __construct(
        protected PreInscripcionService $preInscripcionService
    ) {
    }

    /**
     * Display a listing of pre-inscripciones with payment status
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $preInscripciones = $this->preInscripcionService->getAllWithPaymentStatus();
            return $this->successResponse($preInscripciones);
        }, 'Error al obtener las PreInscripciones');
    }

    /**
     * Get summary of pre-inscripciones
     */
    public function resumenPreinscripcion()
    {
        return $this->handleRequest(function () {
            $resumen = $this->preInscripcionService->getResumen();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de PreInscripciones');
    }

    /**
     * Get table summary by programa
     */
    public function resumenTablaPreInscripcion()
    {
        return $this->handleRequest(function () {
            $resumen = $this->preInscripcionService->getResumenTabla();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de tabla');
    }

    /**
     * Store a newly created pre-inscripcion
     */
    public function store(StorePreInscripcionRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $preInscripcion = $this->preInscripcionService->create($request->validated());

            $this->logActivity('Pre-inscripción creada', null, [
                'preinscripcion_id' => $preInscripcion->id,
                'num_iden' => $preInscripcion->num_iden,
            ]);

            return $this->successResponse($preInscripcion, 'Pre-inscripción registrada exitosamente', 201);
        }, 'Error al crear la pre-inscripción');
    }

    /**
     * Display the specified pre-inscripcion by num_iden
     */
    public function show($numIden)
    {
        return $this->handleRequest(function () use ($numIden) {
            $preInscripcion = $this->preInscripcionService->findByNumIden($numIden);

            if (!$preInscripcion) {
                return $this->errorResponse("La pre-inscripción con num_iden {$numIden} no existe", 404);
            }

            return $this->successResponse($preInscripcion);
        }, 'Error al mostrar la pre-inscripción');
    }

    /**
     * Update the specified pre-inscripcion
     */
    public function update(UpdatePreInscripcionRequest $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $preInscripcion = $this->preInscripcionService->update($id, $request->validated());

            if (!$preInscripcion) {
                return $this->errorResponse("La pre-inscripción con ID {$id} no existe", 404);
            }

            $this->logActivity('Pre-inscripción actualizada', null, [
                'preinscripcion_id' => $id,
            ]);

            return $this->successResponse($preInscripcion, 'Pre-inscripción actualizada exitosamente');
        }, 'Error al actualizar la pre-inscripción');
    }

    /**
     * Deactivate the specified pre-inscripcion
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->preInscripcionService->deactivate($id);

            if (!$resultado) {
                return $this->errorResponse("La pre-inscripción con ID {$id} no existe", 404);
            }

            $this->logActivity('Pre-inscripción inactivada', null, [
                'preinscripcion_id' => $id,
            ]);

            return $this->successResponse(null, 'Pre-inscripción inactivada exitosamente');
        }, 'Error al inactivar la pre-inscripción');
    }

    /**
     * Generate Excel report
     */
    public function report(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $gradoId = intval($request->input('grado'));
            $programaId = intval($request->input('programa'));

            $this->logActivity('Reporte de pre-inscripciones generado', null, [
                'grado_id' => $gradoId,
                'programa_id' => $programaId,
            ]);

            return $this->preInscripcionService->generateReport($gradoId, $programaId);
        }, 'Error al generar el reporte de pre-inscripciones');
    }

    /**
     * Generate daily report
     */
    public function reportDiario()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte diario de pre-inscripciones generado');
            return $this->preInscripcionService->generateDailyReport();
        }, 'Error al generar el reporte diario');
    }

    /**
     * Generate daily faculty report
     */
    public function reportDiarioFacultad()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte diario por facultad generado');
            return $this->preInscripcionService->generateDailyFacultyReport();
        }, 'Error al generar el reporte por facultad');
    }

    /**
     * Check if pre-inscrito exists
     */
    public function preInscrito(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'num_iden' => 'required|string|max:20',
            ]);

            $resultado = $this->preInscripcionService->checkPreInscrito($validated['num_iden']);
            return $this->successResponse($resultado, $resultado['message']);
        }, 'Error en la consulta de pre-inscripción');
    }

    /**
     * Get general summary for comision
     */
    public function resumenGeneralPreinscripcion()
    {
        return $this->handleRequest(function () {
            $resumen = $this->preInscripcionService->getResumenGeneral();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen general');
    }
}
