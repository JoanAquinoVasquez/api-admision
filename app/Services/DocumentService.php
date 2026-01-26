<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\Postulante;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    /**
     * Store document locally and create record
     */
    public function storeDocument(Postulante $postulante, string $tipo, UploadedFile $file): Documento
    {
        $fileExtension = $file->getClientOriginalExtension();
        $timestamp = Carbon::now()->format('His_dmy');
        $fileName = "{$postulante->num_iden}_{$timestamp}.{$fileExtension}";
        
        // Determinar carpeta según tipo
        $folder = $tipo === 'Foto' ? 'fotos' : 'temp';
        $disk = $tipo === 'Foto' ? 'public' : 'local';
        
        $path = $file->storeAs($folder, $fileName, $disk);
        
        $url = $tipo === 'Foto' ? asset('storage/' . $path) : null;
        $nombreArchivo = $tipo === 'Foto' ? 'storage/' . $path : $path;

        return Documento::create([
            'postulante_id' => $postulante->id,
            'tipo' => $tipo,
            'nombre_archivo' => $nombreArchivo,
            'url' => $url,
        ]);
    }

    /**
     * Store temporary files for job processing
     */
    public function storeTempFiles(array $files): array
    {
        $tempPaths = [];
        
        foreach ($files as $tipo => $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('temp');
                $tempPaths[] = [
                    'tipo' => $tipo,
                    'path' => $path,
                ];
            }
        }
        
        return $tempPaths;
    }

    /**
     * Update document (delete old, store new)
     */
    public function updateDocument(Postulante $postulante, string $tipo, UploadedFile $file): Documento
    {
        // Buscar documento existente
        $documento = Documento::where('postulante_id', $postulante->id)
            ->where('tipo', $tipo)
            ->first();

        // Eliminar archivo anterior si existe
        if ($documento && $documento->nombre_archivo) {
            $this->deleteFile($documento->nombre_archivo, $tipo === 'Foto' ? 'public' : 'local');
            $documento->delete();
        }

        return $this->storeDocument($postulante, $tipo, $file);
    }

    /**
     * Delete file from storage
     */
    private function deleteFile(string $path, string $disk): void
    {
        // Ajustar path si tiene prefijo storage/
        $cleanPath = str_replace('storage/', '', $path);
        
        if (Storage::disk($disk)->exists($cleanPath)) {
            Storage::disk($disk)->delete($cleanPath);
        }
    }
}
