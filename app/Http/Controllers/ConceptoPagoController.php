<?php

namespace App\Http\Controllers;

use App\Models\ConceptoPago;
use Illuminate\Http\Request;

class ConceptoPagoController extends BaseController
{
    /**
     * Display a listing of all conceptos de pago
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $conceptos = ConceptoPago::all();
            return $this->successResponse($conceptos);
        }, 'Error al obtener los conceptos de pago');
    }

    /**
     * Store a newly created concepto de pago
     */
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'cod_concepto' => 'required|string|max:255',
                'nombre' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'estado' => 'nullable|boolean',
            ]);

            $concepto = ConceptoPago::create($validated);

            $this->logActivity('Concepto de pago creado', null, [
                'concepto_id' => $concepto->id,
                'nombre' => $concepto->nombre,
            ]);

            return $this->successResponse($concepto, 'Concepto de pago creado exitosamente', 201);
        }, 'Error al crear el concepto de pago');
    }

    /**
     * Display the specified concepto de pago
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $concepto = ConceptoPago::find($id);

            if (!$concepto) {
                return $this->errorResponse("El concepto de pago con ID {$id} no existe", 404);
            }

            return $this->successResponse($concepto);
        }, 'Error al mostrar el concepto de pago');
    }

    /**
     * Update the specified concepto de pago
     */
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $concepto = ConceptoPago::find($id);

            if (!$concepto) {
                return $this->errorResponse("El concepto de pago con ID {$id} no existe", 404);
            }

            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'monto' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|boolean',
            ]);

            $concepto->update($validated);

            $this->logActivity('Concepto de pago actualizado', null, [
                'concepto_id' => $id,
            ]);

            return $this->successResponse($concepto, 'Concepto de pago actualizado exitosamente');
        }, 'Error al actualizar el concepto de pago');
    }

    /**
     * Deactivate the specified concepto de pago
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $concepto = ConceptoPago::find($id);

            if (!$concepto) {
                return $this->errorResponse("El concepto de pago con ID {$id} no existe", 404);
            }

            $concepto->update(['estado' => false]);

            $this->logActivity('Concepto de pago inactivado', null, [
                'concepto_id' => $id,
            ]);

            return $this->successResponse(null, 'Concepto de pago inactivado exitosamente');
        }, 'Error al inactivar el concepto de pago');
    }
}
