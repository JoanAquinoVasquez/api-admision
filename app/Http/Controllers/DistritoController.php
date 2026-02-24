<?php

namespace App\Http\Controllers;

use App\Models\Distrito;

class DistritoController extends BaseController
{
    /**
     * Get distritos by provincia
     */
    public function showProvincia($id)
    {
        return $this->handleRequest(function () use ($id) {
            $distritos = Distrito::where('provincia_id', $id)->orderBy('nombre', 'asc')->get();
            return $this->successResponse($distritos);
        }, 'Error al obtener los distritos');
    }

    /**
     * Display the specified distrito
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $distrito = Distrito::find($id);

            if (!$distrito) {
                return $this->errorResponse("El distrito con ID {$id} no existe", 404);
            }

            return $this->successResponse($distrito);
        }, 'Error al mostrar el distrito');
    }
}
