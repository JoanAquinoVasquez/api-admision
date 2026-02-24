<?php

namespace App\Http\Controllers;

use App\Models\Departamento;

class DepartamentoController extends BaseController
{
    /**
     * Display a listing of all departamentos
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $departamentos = Departamento::orderBy('nombre', 'asc')->get();
            return $this->successResponse($departamentos);
        }, 'Error al obtener los departamentos');
    }

    /**
     * Display the specified departamento
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $departamento = Departamento::find($id);

            if (!$departamento) {
                return $this->errorResponse("El departamento con ID {$id} no existe", 404);
            }

            return $this->successResponse($departamento);
        }, 'Error al mostrar el departamento');
    }
}
