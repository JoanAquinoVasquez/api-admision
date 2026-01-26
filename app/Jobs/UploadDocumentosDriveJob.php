<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Models\Postulante;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;

class UploadDocumentosDriveJob implements ShouldQueue
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
        try {
            foreach ($this->documentos as $doc) {
                $filePath = Storage::path($doc['path']);

                if (!file_exists($filePath)) {
                    Log::error("Archivo no encontrado: {$filePath}");
                    continue;
                }

                $file = new File($filePath); // Correcto aquí

                $upload = $driveService->upload($file, $this->gradoId, $doc['tipo'], $this->postulante->num_iden);

                Documento::create([
                    'postulante_id'  => $this->postulante->id,
                    'tipo'           => $doc['tipo'],
                    'nombre_archivo' => $upload['fileName'],
                    'url'            => $upload['url'],
                ]);

                unlink($filePath); // Limpieza opcional
            }
        } catch (\Throwable $e) {
            Log::error('Error en UploadDocumentosDriveJob: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e; // importante: relanza la excepción para que el job falle y Laravel lo registre
        }
    }
}
