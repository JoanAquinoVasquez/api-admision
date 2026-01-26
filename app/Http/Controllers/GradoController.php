<?php

namespace App\Http\Controllers;

use App\Models\Grado;

class GradoController extends BaseController
{
    /**
     * Display a listing of all grados
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $grados = Grado::all();
            return $this->successResponse($grados);
        }, 'Error al obtener los grados');
    }

    /**
     * Display the specified grado
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $grado = Grado::find($id);

            if (!$grado) {
                return $this->errorResponse("El grado con ID {$id} no existe", 404);
            }

            return $this->successResponse($grado);
        }, 'Error al mostrar el grado');
    }
}
