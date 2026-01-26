<?php

namespace App\Actions\Inscripcion;

use App\DTOs\InscripcionData;
use App\Jobs\SendInscripcionEmailJob;
use App\Jobs\UploadDocumentosDriveJob;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use App\Services\DocumentService;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInscripcionAction
{
    public function __construct(
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected PostulanteRepositoryInterface $postulanteRepository,
        protected ProgramaRepositoryInterface $programaRepository,
        protected VoucherService $voucherService,
        protected DocumentService $documentService
    ) {}

    public function execute(InscripcionData $data): array
    {
        try {
            DB::beginTransaction();

            // 1. Validar Voucher
            $voucher = $this->voucherService->findVoucher($data->tipoPago, $data->codVoucher, $data->numIden);
            
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
            } else {
                // Crear Postulante
                $postulante = $this->postulanteRepository->create($data->toPostulanteArray());
            }

            // 3. Obtener Programa
            $programa = $this->programaRepository->find($data->programaId);

            // 4. Crear Inscripción
            $inscripcion = $this->inscripcionRepository->create([
                'programa_id'   => $programa->id,
                'postulante_id' => $postulante->id,
                'voucher_id'    => $voucher->id,
                'codigo'        => $data->codVoucher,
                'val_digital'   => 0,
                'val_fisico'    => false,
                'estado'        => $programa->estado,
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

            // 8. Log Activity
            activity()
                ->performedOn($inscripcion)
                ->withProperties(['subject' => $data->toPostulanteArray()])
                ->log('Correo de confirmación de inscripción enviado');

            DB::commit();

            // 9. Dispatch Jobs
            DB::afterCommit(function () use ($postulante, $tempPaths, $programa, $data) {
                // Subir a Drive
                UploadDocumentosDriveJob::dispatch($postulante, $tempPaths, $programa->grado_id);

                // Enviar Email
                $url = config("admission.programa_urls.{$programa->id}");
                $nombre_programa = mb_strtoupper($programa->nombre, 'UTF-8');
                $nombre_grado = $programa->grado->nombre;
                
                $cleanData = $data->toPostulanteArray();
                $cleanData['programa_id'] = $data->programaId;
                $cleanData['cod_voucher'] = $data->codVoucher;

                SendInscripcionEmailJob::dispatch($data->email, $cleanData, $nombre_programa, $nombre_grado, $url);
            });

            return [
                'success' => true,
                'message' => 'Inscripción nueva registrada exitosamente y correo de inscripción enviado',
                'data'    => $data->toPostulanteArray(),
            ];

        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear la inscripción: ' . $e->getMessage(),
            ];
        }
    }
}
