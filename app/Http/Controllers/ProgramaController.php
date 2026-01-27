<?php

namespace App\Http\Controllers;

use App\Services\ProgramaService;
use Illuminate\Http\Request;

class ProgramaController extends BaseController
{
    public function __construct(
        protected ProgramaService $programaService
    ) {
    }

    /**
     * Display a listing of all programs
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $programas = $this->programaService->getAllWithRelations();
            return $this->successResponse($programas);
        }, 'Error al obtener los programas');
    }

    /**
     * Get programs optimized for landing pages (minimal data)
     */
    public function forLandingPages()
    {
        return $this->handleRequest(function () {
            $programas = $this->programaService->getForLandingPages();
            return $this->successResponse($programas);
        }, 'Error al obtener los programas');
    }

    /**
     * Get enabled programs with inscription count
     */
    public function programasHabilitados()
    {
        return $this->handleRequest(function () {
            $programas = $this->programaService->getEnabledPrograms();
            return $this->successResponse($programas);
        }, 'Error al obtener los programas habilitados');
    }

    /**
     * Store a newly created program
     */
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'grado_id' => 'required|exists:grados,id',
                'facultad_id' => 'required|exists:facultads,id',
                'nombre' => 'required|string|max:255',
                'vacantes' => 'required|string|max:255',
                'estado' => 'nullable|boolean',
            ]);

            $programa = $this->programaService->createProgram($validated);

            $this->logActivity('Programa creado', null, [
                'programa_id' => $programa->id,
                'nombre' => $programa->nombre,
            ]);

            return $this->successResponse($programa, 'Programa creado exitosamente', 201);
        }, 'Error al crear el programa');
    }

    /**
     * Display the specified program
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $programa = $this->programaService->getProgramById($id);
            if (!$programa) {
                return $this->errorResponse("El programa con ID {$id} no existe", 404);
            }
            return $this->successResponse($programa);

        }, 'Error al obtener los detalles del programa');
    }
    /**
     * Update the specified program
     */
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'grado_id' => 'sometimes|exists:grados,id',
                'facultad_id' => 'sometimes|exists:facultads,id',
                'nombre' => 'sometimes|string|max:255',
                'vacantes' => 'sometimes|string|max:255',
                'estado' => 'sometimes|boolean',
            ]);

            $programa = $this->programaService->updateProgram($id, $validated);

            if (!$programa) {
                return $this->errorResponse("El programa con ID {$id} no existe", 404);
            }

            $this->logActivity('Programa actualizado', null, [
                'programa_id' => $id,
            ]);

            return $this->successResponse($programa, 'Programa actualizado exitosamente');
        }, 'Error al actualizar el programa');
    }

    /**
     * Remove the specified program (soft delete)
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->programaService->deactivateProgram($id);

            if (!$resultado) {
                return $this->errorResponse("El programa con ID {$id} no existe", 404);
            }

            $this->logActivity('Programa desactivado', null, [
                'programa_id' => $id,
            ]);

            return $this->successResponse(null, 'Programa desactivado exitosamente');
        }, 'Error al desactivar el programa');
    }

    public function preInscritosTotales()
    {

        return $this->handleRequest(function () {
            $datos = $this->programaService->getPreInscritosTotal();
            return $this->successResponse($datos);
        }, 'Error al obtener los pre-inscritos');
    }
}
