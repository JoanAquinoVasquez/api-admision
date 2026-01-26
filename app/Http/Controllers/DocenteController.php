<?php

namespace App\Http\Controllers;

use App\Services\DocenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocenteController extends BaseController
{
    public function __construct(
        protected DocenteService $docenteService
    ) {
    }

    /**
     * Display a listing of docentes
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $docentes = $this->docenteService->getAll();
            return $this->successResponse($docentes);
        }, 'Error al obtener las Docentes');
    }

    /**
     * Display the specified docente
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $docente = $this->docenteService->getById($id);

            if (!$docente) {
                return $this->errorResponse("El docente con ID {$id} no existe", 404);
            }

            return $this->successResponse($docente);
        }, 'Error al obtener el Docente');
    }

    /**
     * Store a newly created docente
     */
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'nombres' => 'required|string|max:255',
                'ap_paterno' => 'required|string|max:255',
                'ap_materno' => 'required|string|max:255',
                'dni' => 'required|string|max:8|unique:docentes,dni',
                'email' => 'required|email|max:255|unique:docentes,email',
                'password' => 'required|string|min:8',
            ]);

            $docente = $this->docenteService->create($validated);

            $this->logActivity('Docente creado', null, [
                'docente_id' => $docente->id,
                'email' => $docente->email,
            ]);

            return $this->successResponse($docente, 'Docente creado exitosamente', 201);
        }, 'Error al crear el Docente');
    }

    /**
     * Update the specified docente
     */
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'nombres' => 'sometimes|string|max:255',
                'ap_paterno' => 'sometimes|string|max:255',
                'ap_materno' => 'sometimes|string|max:255',
                'dni' => 'sometimes|string|max:20|unique:docentes,dni,' . $id,
                'email' => 'sometimes|email|max:255|unique:docentes,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'estado' => 'sometimes|boolean',
            ]);

            $docente = $this->docenteService->update($id, $validated);

            if (!$docente) {
                return $this->errorResponse("El docente con ID {$id} no existe", 404);
            }

            $this->logActivity('Docente actualizado', null, [
                'docente_id' => $id,
            ]);

            return $this->successResponse($docente, 'Docente actualizado exitosamente');
        }, 'Error al actualizar el Docente');
    }

    /**
     * Deactivate the specified docente
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->docenteService->deactivate($id);

            if (!$resultado) {
                return $this->errorResponse("El docente con ID {$id} no existe", 404);
            }

            $this->logActivity('Docente inactivado', null, [
                'docente_id' => $id,
            ]);

            return $this->successResponse(null, 'Docente inactivado exitosamente');
        }, 'Error al eliminar el Docente');
    }

    /**
     * Assign programas to docente
     */
    public function asignarPrograma(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'programas' => 'required|array',
                'programas.*' => 'required|integer|exists:programas,id',
            ]);

            $docente = $this->docenteService->assignProgramas($id, $validated['programas']);

            $this->logActivity('Programas asignados a docente', null, [
                'docente_id' => $id,
                'programas' => $validated['programas'],
            ]);

            return $this->successResponse($docente, 'Programas asignados exitosamente');
        }, 'Error al asignar los programas al Docente');
    }

    /**
     * Get programas assigned to authenticated docente
     */
    public function programasAsignados()
    {
        return $this->handleRequest(function () {
            $docenteId = Auth::guard('docente')->id();

            if (!$docenteId) {
                return $this->errorResponse('No autenticado', 401);
            }

            $programas = $this->docenteService->getProgramasAsignados($docenteId);
            return $this->successResponse($programas);
        }, 'Error al obtener programas asignados');
    }

    /**
     * Get postulantes aptos for a programa
     */
    public function postulantesAptos($id)
    {
        return $this->handleRequest(function () use ($id) {
            $postulantes = $this->docenteService->getPostulantesAptos($id);
            return $this->successResponse($postulantes);
        }, 'Error al obtener postulantes aptos');
    }

    /**
     * Register CV grade for postulante
     */
    public function registrarNota(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'postulante_id' => 'required|exists:inscripcions,postulante_id',
                'notaCv' => 'required|numeric|min:0|max:30',
            ]);

            $nota = $this->docenteService->registrarNota(
                $validated['postulante_id'],
                $validated['notaCv']
            );

            $this->logActivity('Nota CV registrada', null, [
                'postulante_id' => $validated['postulante_id'],
                'nota_cv' => $validated['notaCv'],
            ]);

            return $this->successResponse($nota, 'Nota registrada correctamente');
        }, 'Error al registrar la nota');
    }

    /**
     * Generate CV grades report PDF
     */
    public function reportNotasCV($idPrograma)
    {
        try {
            $pdf = $this->docenteService->generateReportNotasCV($idPrograma);

            if (!$pdf) {
                return $this->errorResponse('No hay notas registradas', 404);
            }

            $this->logActivity('Reporte de notas CV generado', null, [
                'programa_id' => $idPrograma,
            ]);

            return $pdf->stream("notasCV-postulantes.pdf");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar el reporte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate multiple CV grades report PDF
     */
    public function reportNotasCVMultiple(Request $request)
    {
        try {
            $idProgramas = $request->input('ids');

            if (empty($idProgramas) || !is_array($idProgramas)) {
                return $this->errorResponse('No se enviaron programas válidos', 400);
            }

            $pdf = $this->docenteService->generateReportNotasCVMultiple($idProgramas);

            if (!$pdf) {
                return $this->errorResponse('No hay postulantes aptos registrados para los programas seleccionados', 404);
            }

            $this->logActivity('Reporte múltiple de notas CV generado', null, [
                'programas_count' => count($idProgramas),
            ]);

            return $pdf->stream("plantilla_entrevista.pdf");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar el reporte múltiple: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get summary of docente grades
     */
    public function resumenDocenteNotas()
    {
        return $this->handleRequest(function () {
            $resumen = $this->docenteService->getResumenDocenteNotas();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de notas');
    }
}
