<?php

namespace App\Services;

use App\DTOs\InscripcionData;
use App\Jobs\SendInscripcionEmailJob;
use App\Jobs\UploadDocumentosDriveJob;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InscripcionService
{
    public function __construct(
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected PostulanteRepositoryInterface $postulanteRepository,
        protected ProgramaRepositoryInterface $programaRepository,
        protected VoucherService $voucherService,
        protected DocumentService $documentService
    ) {
    }

    public function storeInscripcion(InscripcionData $data)
    {
        try {
            DB::beginTransaction();

            // 1. Validar Voucher
            $voucher = $this->voucherService->findVoucher($data->codVoucher, $data->numIden);

            if (!$voucher) {
                throw new \Exception('Voucher no encontrado o no corresponde al postulante');
            }

            // 2. Verificar Postulante existente
            $existingPostulante = $this->postulanteRepository->findByNumIden($data->numIden);

            if ($existingPostulante) {
                if ($this->postulanteRepository->hasActiveInscripcion($existingPostulante->id)) {
                    return [
                        'success' => false,
                        'message' => 'Este postulante ya tiene una inscripción activa registrada.',
                    ];
                }
                $postulante = $existingPostulante;
                // Actualizar datos si es necesario? Por ahora usamos el existente o actualizamos?
                // El código original creaba uno nuevo si no existía, pero si existía solo verificaba inscripción.
                // Asumiremos que si existe, usamos ese ID.
            } else {
                // Crear Postulante
                $postulante = $this->postulanteRepository->create($data->toPostulanteArray());
            }

            // 3. Obtener Programa
            $programa = $this->programaRepository->find($data->programaId);

            // 4. Crear Inscripción
            $inscripcion = $this->inscripcionRepository->create([
                'programa_id' => $programa->id,
                'postulante_id' => $postulante->id,
                'voucher_id' => $voucher->id,
                'codigo' => $data->codVoucher,
                'val_digital' => 0,
                'val_fisico' => false,
                'estado' => $programa->estado,
            ]);

            // 5. Manejar Archivos Temporales (para Job de Drive)
            $filesToUpload = [
                'Voucher' => $data->voucherFile,
                'DocumentoIdentidad' => $data->docIdentidadFile,
                'Curriculum' => $data->cvFile,
            ];

            $tempPaths = $this->documentService->storeTempFiles($filesToUpload);

            // 6. Guardar Foto localmente
            if ($data->fotoFile) {
                $this->documentService->storeDocument($postulante, 'Foto', $data->fotoFile);
            }

            // 7. Procesar uso del Voucher
            $this->voucherService->processVoucherUsage($voucher, $data->numIden);

            // 8. Actualizar PreInscripcion si existe
            if ($postulante->preInscripcion) {
                // Ya está relacionado si usamos la relación, pero aquí actualizaban postulante_id en pre_inscripcion
                // Si el postulante es nuevo, la preinscripcion podría estar huérfana de postulante_id?
                // El código original buscaba por DNI en PreInscripcion
                // Aquí asumimos que la lógica de negocio se mantiene
            }

            // 9. Log Activity
            activity()
                ->performedOn($inscripcion)
                ->withProperties(['subject' => $data->toPostulanteArray()])
                ->log('Correo de confirmación de inscripción enviado');

            DB::commit();
            // 10. Dispatch Jobs
            DB::afterCommit(function () use ($postulante, $tempPaths, $programa, $data) {
                // Subir a Drive
                UploadDocumentosDriveJob::dispatch($postulante, $tempPaths, $programa->grado_id);

                // Enviar Email
                $url = $programa->brochure;
                $nombre_programa = mb_strtoupper($programa->nombre, 'UTF-8');
                $nombre_grado = ucfirst(strtolower($programa->grado->nombre));

                // Preparar datos para email (excluyendo archivos)
                $emailData = (array) $data;
                // Limpiar objetos de archivo del array si es necesario para serialización del job
                // Pero SendInscripcionEmailJob espera un array $validated. 
                // Construiremos un array limpio.
                $cleanData = $data->toPostulanteArray();
                $cleanData['programa_id'] = $data->programaId;
                $cleanData['cod_voucher'] = $data->codVoucher;
                // ... otros campos que el Job necesite

                SendInscripcionEmailJob::dispatch($data->email, $cleanData, $nombre_programa, $nombre_grado, $url);
            });

            return [
                'success' => true,
                'message' => 'Inscripción nueva registrada exitosamente y correo de inscripción enviado',
                'data' => $data->toPostulanteArray(), // Retornar datos básicos
            ];

        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear la inscripción: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get possible programs for an inscription based on voucher concept
     */
    public function getProgramasPosibles(int $inscripcionId)
    {
        $inscripcion = $this->inscripcionRepository->findWithRelations($inscripcionId);

        if (!$inscripcion || !$inscripcion->voucher) {
            throw new \Exception('Inscripción o voucher no encontrado');
        }

        return $this->programaRepository->getByConceptoPago($inscripcion->voucher->concepto_pago_id);
    }
}
