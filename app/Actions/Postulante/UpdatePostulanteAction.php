<?php

namespace App\Actions\Postulante;

use App\Models\Documento;
use App\Models\Postulante;
use App\Models\Programa;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use App\Services\GoogleDriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatePostulanteAction
{
    public function __construct(
        protected PostulanteRepositoryInterface $postulanteRepository,
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected GoogleDriveService $googleDriveService
    ) {}

    public function execute(int $postulanteId, array $data, array $files = []): array
    {
        try {
            DB::beginTransaction();

            $postulante = $this->postulanteRepository->find($postulanteId);

            if (!$postulante) {
                throw new \Exception('Postulante no encontrado');
            }

            $inscripcion = $postulante->inscripcion;
            if (!$inscripcion) {
                throw new \Exception('Inscripción no encontrada');
            }

            // 1. Actualizar datos del postulante
            $postulante->update($data);

            // 2. Actualizar inscripción (programa) y mover documentos si es necesario
            if (isset($data['id_programa'])) {
                $oldGradoId = $inscripcion->programa->grado->id;
                
                $inscripcion->update([
                    'programa_id' => $data['id_programa'],
                ]);
                
                // Refrescar para obtener el nuevo programa/grado
                $inscripcion->refresh();
                $newGradoId = $inscripcion->programa->grado->id;

                if ($oldGradoId != $newGradoId) {
                    // Mover documentos a la carpeta del nuevo grado
                    // Pasamos los archivos que se están subiendo nuevos para NO mover los viejos de esos tipos
                    $filesKeys = array_keys($files);
                    // Mapeo de keys de archivos a nombres de inputs esperados por moverDocumentos
                    // En el controller original: rutaDocIden, rutaFoto, rutaCV, rutaVoucher
                    // En GoogleDriveService::moverDocumentos espera array de keys como 'rutaVoucher', etc.
                    $this->googleDriveService->moverDocumentos($postulante->id, $newGradoId, $filesKeys);
                }
            }

            // 3. Manejar archivos nuevos
            $this->handleFiles($postulante, $files);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Inscripción actualizada exitosamente',
                'data' => [
                    'postulante' => $postulante,
                    'inscripcion' => $inscripcion
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating postulante: " . $e->getMessage());
            throw $e;
        }
    }

    protected function handleFiles(Postulante $postulante, array $files)
    {
        $fileTypes = [
            'rutaDocIden' => 'DocumentoIdentidad',
            'rutaFoto' => 'Foto',
            'rutaCV' => 'Curriculum',
            'rutaVoucher' => 'Voucher',
        ];

        foreach ($files as $key => $file) {
            if (!isset($fileTypes[$key]) || !$file instanceof UploadedFile) {
                continue;
            }

            $fileType = $fileTypes[$key];
            
            // Lógica específica para Foto (local storage en original, pero GoogleDriveService parece manejar todo en Drive ahora?)
            // El controlador original manejaba Foto en local storage ('fotos/') y otros en Drive.
            // Sin embargo, GoogleDriveService tiene lógica para 'Foto' en subcarpetas? 
            // Revisando GoogleDriveService: subFolderName match no incluye 'Foto' explícitamente en 'upload', devuelve '' default.
            // Pero el controlador original para 'rutaFoto' usaba storage local.
            // Vamos a mantener la lógica del controlador original para la FOTO si es local, 
            // o migrarla a Drive si esa era la intención.
            // El controlador original dice:
            // $path = $foto->storeAs('fotos', $fileName, 'public');
            // $documento->update(['nombre_archivo' => 'fotos/' . $fileName, 'url' => asset(...)]);
            
            // Para los otros (CV, Voucher, DNI) usa handleNewFile -> uploadToGoogleDrive.
            
            if ($fileType === 'Foto') {
                $this->handlePhoto($postulante, $file);
            } else {
                $this->handleDriveFile($postulante, $fileType, $file);
            }
        }
    }

    protected function handlePhoto(Postulante $postulante, UploadedFile $file)
    {
        $documento = Documento::where('postulante_id', $postulante->id)
            ->where('tipo', 'Foto')
            ->where('estado', true)
            ->first();

        if ($documento) {
            // Eliminar archivo anterior local
            $filePath = storage_path('app/public/' . $documento->nombre_archivo);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } else {
            // Crear registro si no existe (aunque debería existir)
             // Esto podría requerir lógica adicional si no existe el documento
        }

        $fileExtension = $file->getClientOriginalExtension();
        $timestamp = now()->format('His_dmy');
        $fileName = "{$postulante->num_iden}_{$timestamp}.{$fileExtension}";
        $file->storeAs('fotos', $fileName, 'public');

        if ($documento) {
            $documento->update([
                'nombre_archivo' => 'fotos/' . $fileName,
                'url' => asset('storage/fotos/' . $fileName)
            ]);
        }
    }

    protected function handleDriveFile(Postulante $postulante, string $fileType, UploadedFile $file)
    {
        $documento = Documento::where('postulante_id', $postulante->id)
            ->where('tipo', $fileType)
            ->where('estado', true)
            ->first();

        if ($documento) {
            // Eliminar archivo anterior de Drive
            // Necesitamos el path o ID. El modelo guarda el path en nombre_archivo o URL.
            // GoogleDriveService no tiene delete explícito público, pero podemos usar Storage::disk('google')->delete()
            // O agregar delete a GoogleDriveService.
            // Por ahora usaremos Storage facade aquí o asumiremos que upload reemplaza? No, upload crea nuevo.
            
            if ($documento->nombre_archivo) {
                 \Illuminate\Support\Facades\Storage::disk('google')->delete($documento->nombre_archivo);
            }
        }

        // Subir nuevo
        $gradoId = $postulante->inscripcion->programa->grado->id;
        $result = $this->googleDriveService->upload($file, $gradoId, $fileType, $postulante->num_iden);

        if ($documento) {
            $documento->update([
                'nombre_archivo' => $result['fileName'],
                'url' => $result['url']
            ]);
        }
    }
}
