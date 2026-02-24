<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\FacultadRepositoryInterface;
use Illuminate\Http\Request;

class FacultadController extends BaseController
{
    public function __construct(
        protected FacultadRepositoryInterface $facultadRepository
    ) {
    }

    /**
     * Display a listing of all facultades
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $facultades = $this->facultadRepository->all();
            return $this->successResponse($facultades);
        }, 'Error al obtener las facultades');
    }

    /**
     * Display the specified facultad
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $facultad = $this->facultadRepository->find($id);

            if (!$facultad) {
                return $this->errorResponse("La facultad con ID {$id} no existe", 404);
            }

            return $this->successResponse($facultad);
        }, 'Error al mostrar la facultad');
    }

    /**
     * Store a newly created facultad
     */
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'siglas' => 'required|string|max:50',
            ]);

            $facultad = $this->facultadRepository->create($validated);

            $this->logActivity('Facultad creada', null, [
                'facultad_id' => $facultad->id,
                'nombre' => $facultad->nombre,
            ]);

            return $this->successResponse($facultad, 'Facultad creada exitosamente', 201);
        }, 'Error al crear la facultad');
    }

    /**
     * Update the specified facultad
     */
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'siglas' => 'sometimes|string|max:50',
            ]);

            $facultad = $this->facultadRepository->update($id, $validated);

            if (!$facultad) {
                return $this->errorResponse("La facultad con ID {$id} no existe", 404);
            }

            $this->logActivity('Facultad actualizada', null, [
                'facultad_id' => $id,
            ]);

            return $this->successResponse($facultad, 'Facultad actualizada exitosamente');
        }, 'Error al actualizar la facultad');
    }

    /**
     * Remove the specified facultad
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $resultado = $this->facultadRepository->delete($id);

            if (!$resultado) {
                return $this->errorResponse("La facultad con ID {$id} no existe", 404);
            }

            $this->logActivity('Facultad eliminada', null, [
                'facultad_id' => $id,
            ]);

            return $this->successResponse(null, 'Facultad eliminada exitosamente');
        }, 'Error al eliminar la facultad');
    }
}
