<?php

namespace App\Http\Controllers;

use App\Services\ReservaDevolucionService;
use Illuminate\Http\Request;

class ReservaDevolucionController extends BaseController
{
    public function __construct(
        protected ReservaDevolucionService $reservaDevolucionService
    ) {
    }

    /**
     * Disable inscriptions for programs
     */
    public function inhabilitarInscripciones(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $ids = $request->input('ids');

            if (empty($ids)) {
                return $this->errorResponse('No se proporcionaron IDs', 400);
            }

            $resultado = $this->reservaDevolucionService->inhabilitarInscripciones($ids);

            if (!$resultado) {
                return $this->errorResponse('No se encontraron programas con los IDs proporcionados', 404);
            }

            $programasInhabilitados = $resultado['programas_inhabilitados'];
            $inscripcionesInhabilitadas = $resultado['inscripciones_inhabilitadas'];

            if ($programasInhabilitados->isNotEmpty()) {
                $nombresConGrado = $programasInhabilitados->map(function ($programa) {
                    $grado = $programa->grado->nombre ?? 'Sin grado';
                    return "{$grado} en {$programa->nombre}";
                })->join(', ');

                $this->logActivity('Inscripciones inhabilitadas para programas', null, [
                    'programas' => [
                        'ids' => $programasInhabilitados->pluck('id')->join(', '),
                        'nombres' => $nombresConGrado,
                    ],
                    'cantidad_programas_inhabilitados' => $programasInhabilitados->count(),
                    'cantidad_inscripciones_inhabilitadas' => $inscripcionesInhabilitadas->count(),
                ]);
            }

            $message = $programasInhabilitados->isNotEmpty()
                ? "Programas ({$programasInhabilitados->map(fn($p) => $p->nombre . ' (' . ($p->grado->nombre ?? 'Sin grado') . ')')->join(', ')}) inhabilitados" .
                ($inscripcionesInhabilitadas->isNotEmpty() ? " con inscripciones inhabilitadas" : " (sin inscripciones)")
                : 'No se realizaron cambios (todos los programas ya estaban inhabilitados)';

            return $this->successResponse(null, $message);
        }, 'Error al inhabilitar inscripciones');
    }

    /**
     * List all disabled programs
     */
    public function programasInhabilitadosAll()
    {
        return $this->handleRequest(function () {
            $programas = $this->reservaDevolucionService->getProgramasInhabilitados();

            if ($programas->isEmpty()) {
                return $this->successResponse([], 'No se encontraron programas inhabilitados');
            }

            return $this->successResponse($programas);
        }, 'Error al obtener programas inhabilitados');
    }

    /**
     * List disabled programs by grade
     */
    public function programasInhabilitados($idGrado)
    {
        return $this->handleRequest(function () use ($idGrado) {
            $programas = $this->reservaDevolucionService->getProgramasInhabilitados($idGrado);

            if ($programas->isEmpty()) {
                return $this->successResponse([], 'No se encontraron programas inhabilitados');
            }

            return $this->successResponse($programas);
        }, 'Error al obtener programas inhabilitados');
    }

    /**
     * List all disabled inscriptions
     */
    public function inscripcionesInhabilitadas()
    {
        return $this->handleRequest(function () {
            $inscripciones = $this->reservaDevolucionService->getInscripcionesInhabilitadas();

            if ($inscripciones->isEmpty()) {
                return $this->successResponse([], 'No se encontraron inscripciones inhabilitadas');
            }

            return $this->successResponse($inscripciones);
        }, 'Error al obtener inscripciones inhabilitadas');
    }

    /**
     * List disabled inscriptions by program
     */
    public function inscripcionesInhabilitadasPrograma($idPrograma)
    {
        return $this->handleRequest(function () use ($idPrograma) {
            $inscripciones = $this->reservaDevolucionService->getInscripcionesInhabilitadas($idPrograma);

            if ($inscripciones->isEmpty()) {
                return $this->successResponse([], 'No se encontraron inscripciones inhabilitadas');
            }

            return $this->successResponse($inscripciones);
        }, 'Error al obtener inscripciones inhabilitadas');
    }

    /**
     * Reserve an inscription
     */
    public function reservarInscripcion($id)
    {
        return $this->handleRequest(function () use ($id) {
            $inscripcion = $this->reservaDevolucionService->reservarInscripcion($id);

            if (!$inscripcion) {
                return $this->errorResponse('Inscripción no encontrada', 404);
            }

            $this->logActivity('Inscripción reservada', null, ['inscripcion_id' => $id]);

            return $this->successResponse(null, 'Inscripción reservada');
        }, 'Error al reservar inscripción');
    }

    /**
     * List reserved inscriptions
     */
    public function listarInscripcionesReserva($idPrograma)
    {
        return $this->handleRequest(function () use ($idPrograma) {
            $inscripciones = $this->reservaDevolucionService->getInscripcionesReserva($idPrograma);

            if ($inscripciones->isEmpty()) {
                return $this->successResponse([], 'No se encontraron inscripciones en reserva');
            }

            return $this->successResponse($inscripciones);
        }, 'Error al obtener inscripciones en reserva');
    }

    /**
     * Cancel reservation
     */
    public function cancelarReserva($id)
    {
        return $this->handleRequest(function () use ($id) {
            $inscripcion = $this->reservaDevolucionService->cancelarReserva($id);

            if (!$inscripcion) {
                return $this->errorResponse('Inscripción no encontrada', 404);
            }

            $this->logActivity('Reserva cancelada', null, ['inscripcion_id' => $id]);

            return $this->successResponse(null, 'Inscripción cancelada');
        }, 'Error al cancelar reserva');
    }

    /**
     * Generate reservation report
     */
    public function reportReserva()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte de reservas generado', null, []);
            return $this->reservaDevolucionService->generateReportReserva();
        }, 'Error al generar el reporte de inscripciones');
    }

    /**
     * Generate reservation vouchers TXT
     */
    public function reportReservaVouchers()
    {
        return $this->handleRequest(function () {
            $contenido = $this->reservaDevolucionService->generateReservaVouchersTxt();

            $this->logActivity('Reporte de vouchers de reserva generado', null, []);

            return response($contenido)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="vouchers_reserva_' . now()->format('d-m-Y_His') . '.txt"');
        }, 'Error al generar el reporte de vouchers');
    }

    /**
     * Mark inscription for refund
     */
    public function devolverInscripcion($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->reservaDevolucionService->devolverInscripcion($id);

            if ($resultado === null) {
                return $this->errorResponse('Inscripción no encontrada', 404);
            }

            if ($resultado === false) {
                return $this->errorResponse('Inscripción de este programa si se aperturara', 400);
            }

            $this->logActivity('Inscripción marcada para devolución', null, ['inscripcion_id' => $id]);

            return $this->successResponse(null, 'Inscripción registrada exitosamente para su devolución');
        }, 'Error al marcar inscripción para devolución');
    }

    /**
     * List inscriptions marked for refund
     */
    public function listarInscripcionesDevolver($idPrograma)
    {
        return $this->handleRequest(function () use ($idPrograma) {
            $inscripciones = $this->reservaDevolucionService->getInscripcionesDevolver($idPrograma);

            if ($inscripciones->isEmpty()) {
                return $this->successResponse([], 'No se encontraron inscripciones a devolver');
            }

            return $this->successResponse($inscripciones);
        }, 'Error al obtener inscripciones a devolver');
    }

    /**
     * Cancel refund
     */
    public function cancelarDevolucion($id)
    {
        return $this->handleRequest(function () use ($id) {
            $inscripcion = $this->reservaDevolucionService->cancelarDevolucion($id);

            if (!$inscripcion) {
                return $this->errorResponse('Inscripción no encontrada o programa activo', 404);
            }

            $this->logActivity('Devolución cancelada', null, ['inscripcion_id' => $id]);

            return $this->successResponse(null, 'Devolución cancelada');
        }, 'Error al cancelar devolución');
    }

    /**
     * Generate refund report
     */
    public function reportDevolucion()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte de devoluciones generado', null, []);
            return $this->reservaDevolucionService->generateReportDevolucion();
        }, 'Error al generar el reporte de inscripciones');
    }

    /**
     * Show possible programs for program change
     */
    public function showProgramasPosibles($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->reservaDevolucionService->getProgramasPosibles($id);

            if (!$resultado) {
                return $this->errorResponse("Inscripción con id {$id} no encontrada", 404);
            }

            return $this->successResponse($resultado);
        }, 'Error al mostrar la inscripción');
    }

    /**
     * Update program for an inscription
     */
    public function updateProgramasPosibles(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'programa_id' => 'required|exists:programas,id',
            ]);

            $resultado = $this->reservaDevolucionService->updatePrograma($id, $validated['programa_id'], $request);

            if ($resultado === null) {
                return $this->errorResponse("Inscripción con id {$id} no encontrada", 404);
            }

            if ($resultado === false) {
                return $this->errorResponse('El programa seleccionado no está habilitado', 400);
            }

            $inscripcion = $resultado['inscripcion'];
            $programa_old = $resultado['programa_old'];
            $programa_new = $resultado['programa_new'];

            $this->logActivity('Cambio de programa a uno habilitado', $inscripcion, [
                'subject' => [
                    'nombres' => $inscripcion->postulante->nombres,
                    'ap_paterno' => $inscripcion->postulante->ap_paterno,
                    'ap_materno' => $inscripcion->postulante->ap_materno,
                    'num_iden' => $inscripcion->postulante->num_iden,
                    'tipo_doc' => $inscripcion->postulante->tipo_doc,
                ],
                'programa_old' => ['programa_id' => $programa_old->id, 'nombre' => $programa_old->nombre],
                'programa_new' => ['programa_id' => $programa_new->id, 'nombre' => $programa_new->nombre],
            ]);

            return $this->successResponse([
                'programa_old' => $programa_old,
                'programa_new' => $programa_new,
            ], 'Programa cambiado exitosamente');
        }, 'Error al cambiar el programa');
    }
}
