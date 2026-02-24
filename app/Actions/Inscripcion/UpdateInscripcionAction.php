<?php

namespace App\Actions\Inscripcion;

use App\DTOs\InscripcionData;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;

class UpdateInscripcionAction
{
    public function __construct(
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected PostulanteRepositoryInterface $postulanteRepository,
        protected DocumentService $documentService
    ) {}

    public function execute(int $inscripcionId, InscripcionData $data): array
    {
        try {
            DB::beginTransaction();

            $inscripcion = $this->inscripcionRepository->find($inscripcionId);

            if (!$inscripcion) {
                throw new \Exception("Inscripción no encontrada");
            }

            // Actualizar Postulante
            $postulante = $inscripcion->postulante;
            $postulante->update($data->toPostulanteArray());

            // Actualizar documentos si se enviaron nuevos
            if ($data->fotoFile) {
                $this->documentService->updateDocument($postulante, 'Foto', $data->fotoFile);
            }

            // Nota: Para otros documentos, la lógica podría ser similar o manejar subida a Drive
            // Aquí simplificamos asumiendo que la actualización principal es datos y foto.
            // Si se requiere actualizar voucher/cv/dni, se debería integrar con GoogleDriveService/Jobs similar a Create.

            DB::commit();

            return [
                'success' => true,
                'message' => 'Inscripción actualizada correctamente',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar la inscripción: ' . $e->getMessage(),
            ];
        }
    }
}
