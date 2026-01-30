<?php

namespace App\Http\Controllers;

use App\Actions\Postulante\UpdatePostulanteAction;
use App\Models\Postulante;
use App\Services\PDFService;
use Illuminate\Http\Request;

class PostulanteController extends BaseController
{
    public function __construct(
        protected UpdatePostulanteAction $updatePostulanteAction,
        protected PDFService $pdfService
    ) {
    }

    /**
     * Update postulante data
     */
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'id_programa' => 'sometimes|exists:programas,id',
                'nombres' => 'sometimes|string|max:255',
                'ap_paterno' => 'sometimes|string|max:255',
                'ap_materno' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'tipo_doc' => 'sometimes|string|in:DNI,CE,PAS',
                'num_iden' => 'sometimes|string|min:8|max:20',
                'fecha_nacimiento' => 'sometimes|date',
                'sexo' => 'sometimes|string|in:M,F',
                'celular' => 'sometimes|string|regex:/^(\+?\d{1,3}[-.\\s]?)?(\(?\d{2,4}\)?[-.\\s]?)?\d{7,10}$/',
                'distrito_id' => 'sometimes|exists:distritos,id',
                'direccion' => 'sometimes|string|max:255',
                'rutaDocIden' => 'sometimes|file|mimes:pdf|max:10240',
                'rutaFoto' => 'sometimes|file|mimes:jpg,jpeg,png|max:10240',
                'rutaCV' => 'sometimes|file|mimes:pdf|max:10240',
                'rutaVoucher' => 'sometimes|file|mimes:pdf|max:10240',
            ]);

            $result = $this->updatePostulanteAction->execute(
                $id,
                $request->except(['rutaDocIden', 'rutaFoto', 'rutaCV', 'rutaVoucher']),
                $request->allFiles()
            );

            $this->logActivity('Postulante actualizado', null, [
                'postulante_id' => $id,
            ]);

            return $this->successResponse($result);
        }, 'Error al actualizar la inscripción');
    }

    /**
     * Generate constancia PDF for postulante
     */
    public function generateConstancia($id)
    {
        return $this->handleRequest(function () use ($id) {
            $postulante = Postulante::findOrFail($id);

            return $this->pdfService->generateConstancia($postulante);
        }, 'Error al generar la constancia en PDF');
    }

    /**
     * Generate carnets for multiple postulantes
     */
    public function generateCarnet(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $ids = $request->input('ids');

            if (!$ids || !is_array($ids)) {
                return $this->errorResponse('No se proporcionaron IDs válidos', 400);
            }

            $this->logActivity('Carnets generados', null, [
                'cantidad' => count($ids),
            ]);

            return $this->pdfService->generateCarnets($ids);
        }, 'Error al exportar los carnets');
    }
}
