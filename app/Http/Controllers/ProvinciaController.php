<?php

namespace App\Http\Controllers;

use App\Models\Provincia;

class ProvinciaController extends BaseController
{
    /**
     * Get provincias by departamento
     */
    public function showDepartamento($id)
    {
        return $this->handleRequest(function () use ($id) {
            $provincias = Provincia::where('departamento_id', $id)->orderBy('nombre', 'asc')->get();
            return $this->successResponse($provincias);
        }, 'Error al obtener las provincias');
    }

    /**
     * Display the specified provincia
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $provincia = Provincia::find($id);

            if (!$provincia) {
                return $this->errorResponse("La provincia con ID {$id} no existe", 404);
            }

            return $this->successResponse($provincia);
        }, 'Error al mostrar la provincia');
    }
}
