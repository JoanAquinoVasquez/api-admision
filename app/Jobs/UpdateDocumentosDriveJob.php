<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Models\Postulante;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class UpdateDocumentosDriveJob implements ShouldQueue
{
    use Queueable;

    protected $postulante;
    protected $documentos;
    protected $gradoId;

    public function __construct(Postulante $postulante, array $documentos, int $gradoId)
    {
        $this->postulante = $postulante;
        $this->documentos = $documentos;
        $this->gradoId = $gradoId;
    }


    public function handle(GoogleDriveService $driveService)
    {
        foreach ($this->documentos as $doc) {
            try {
                $filePath = Storage::path($doc['path']);

                if (!file_exists($filePath)) {
                    Log::error("Archivo no encontrado: {$filePath}");
                    continue;
                }

                // Buscar documento existente
                $documento = Documento::where('postulante_id', $this->postulante->id)
                    ->where('tipo', $doc['tipo'])
                    ->where('estado', true)
                    ->first();

                if ($documento) {
                    // Eliminar archivo viejo en Google Drive
                    Storage::disk('google')->delete($documento->nombre_archivo);
                }

                // Subir nuevo
                $file = new File($filePath);
                $upload = $driveService->upload($file, $this->gradoId, $doc['tipo'], $this->postulante->num_iden);

                // Actualizar registro
                if ($documento) {
                    $documento->update([
                        'nombre_archivo' => $upload['fileName'],
                        'url'            => $upload['url'],
                    ]);
                }

                // Limpiar archivo temporal
                Storage::delete($doc['path']);
            } catch (\Throwable $e) {
                Log::error("Error actualizando documento {$doc['tipo']}: {$e->getMessage()}");
                throw $e;
            }
        }
    }
}
