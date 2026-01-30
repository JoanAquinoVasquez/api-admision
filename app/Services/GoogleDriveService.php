<?php

namespace App\Services;

use App\Models\Documento;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    /**
     * Sube los archivos a Drive y retorna la url y la ruta en la que se encuentra el archivo
     */
    public function upload(UploadedFile|File $file, int $id_grado, string $fileType, string $num_iden): array
    {
        // Obtener nombre de carpeta del grado desde config
        $folder = config("admission.carpetas_drive.{$id_grado}", 'OTROS');

        // Define las subcarpetas
        $subFolderName = match ($fileType) {
            'Voucher' => 'Voucher',
            'Curriculum' => 'Curriculum',
            'DocumentoIdentidad' => 'DocumentoIdentidad',
            default => '',
        };

        // Crear un nombre único para el archivo
        $timestamp = Carbon::now()->format('His_dmy');
        $fileExtension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->extension();
        $fileName = "{$num_iden}_{$timestamp}.{$fileExtension}";

        // Ruta en Google Drive
        $filePath = "{$folder}/{$subFolderName}/{$fileName}";

        // Subir archivo
        $uploaded = Storage::disk('google')->put($filePath, file_get_contents($file));

        if (!$uploaded) {
            \Illuminate\Support\Facades\Log::error("Error al subir archivo a Google Drive: {$filePath}");
            throw new \Exception("No se pudo subir el archivo a Google Drive: {$fileName}");
        }

        // Obtener URL
        $fileUrl = Storage::disk('google')->url($filePath);

        // Extraer ID
        $fileId = $this->extractGoogleDriveFileId($fileUrl);

        if (!$fileId) {
            \Illuminate\Support\Facades\Log::error("No se pudo extraer ID de archivo de Drive. URL: {$fileUrl} Regex falló.");
        }

        return [
            'url' => $fileId ? "https://drive.google.com/file/d/{$fileId}/view?usp=sharing" : null,
            'fileName' => $filePath,
        ];
    }

    /**
     * Mover archivos de postulante
     */
    public function moverDocumentos(int $id_postulante, int $idGradoNew, array $filesEnviados = []): void
    {
        $carpetasGrados = config('admission.carpetas_drive');

        $tiposDocumentos = [
            'Voucher' => 'Voucher',
            'Curriculum' => 'Curriculum',
            'DocumentoIdentidad' => 'DocumentoIdentidad',
        ];

        $tipoDocumentosMapeados = [
            'Voucher' => 'rutaVoucher',
            'Curriculum' => 'rutaCV',
            'DocumentoIdentidad' => 'rutaDocIden',
        ];

        // Obtener documentos actuales (excepto Foto)
        $documentos = Documento::where('postulante_id', $id_postulante)
            ->where('estado', true)
            ->where('tipo', '!=', 'Foto')
            ->get();

        foreach ($documentos as $documento) {
            $inputName = $tipoDocumentosMapeados[$documento->tipo] ?? '';

            // Si ese tipo de documento no está en la lista de nuevos archivos enviados, lo movemos
            if (!in_array($inputName, $filesEnviados)) {
                $fileId = $this->extractGoogleDriveFileId($documento->url);

                if (!$fileId)
                    continue;

                $carpetaGradoNuevo = $carpetasGrados[$idGradoNew] ?? 'default';
                $carpetaTipoDocumento = $tiposDocumentos[$documento->tipo] ?? 'default';
                $rutaNuevaCarpeta = "{$carpetaGradoNuevo}/{$carpetaTipoDocumento}";

                $fileName = $documento->nombre_archivo;
                // Reemplazar la carpeta raíz en el nombre del archivo
                $newFileName = preg_replace('/^[^\/]+/', $carpetaGradoNuevo, $fileName);

                $documento->update([
                    'nombre_archivo' => $newFileName
                ]);

                $this->moverFile($fileId, $rutaNuevaCarpeta);
            }
        }
    }

    /**
     * Extraer el ID del archivo de una URL de Google Drive.
     */
    private function extractGoogleDriveFileId(string $url): ?string
    {
        // Soporta formatos: /d/ID/ y id=ID
        preg_match('/(?:id=|\/d\/)([\w-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Mueve un archivo de una carpeta a otra en Google Drive
     */
    public function moverFile(string $fileId, string $rutaAMover): array
    {
        $name_folder = env('GOOGLE_DRIVE_FOLDER', 'ProcesoAdmision2025-I');
        $service = Storage::disk('google')->getAdapter()->getService();
        $newFolderId = $this->getFolderIdByPath("$name_folder/" . $rutaAMover, $service);

        if (!$newFolderId) {
            return ['success' => false, 'message' => 'Carpeta destino no encontrada'];
        }

        // Obtener carpeta actual del archivo
        $file = $service->files->get($fileId, ['fields' => 'parents']);
        $previousParents = join(',', $file->parents);

        $driveFile = new DriveFile();
        $updatedFile = $service->files->update(
            $fileId,
            $driveFile,
            [
                'addParents' => $newFolderId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents'
            ]
        );

        return [
            'message' => 'Archivo movido correctamente',
            'file' => $updatedFile
        ];
    }

    /**
     * Obtener el ID de una carpeta en Google Drive por su ruta.
     */
    private function getFolderIdByPath(string $folderPath, $service): ?string
    {
        $folders = explode('/', $folderPath);
        $parentId = 'root';
        $foundId = null;

        foreach ($folders as $folderName) {
            $query = "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '$parentId' in parents";
            $results = $service->files->listFiles(['q' => $query, 'fields' => 'files(id, name)', 'spaces' => 'drive']);

            if (count($results->getFiles()) == 0) {
                return null;
            }

            $foundId = $results->getFiles()[0]->getId();
            $parentId = $foundId;
        }

        return $foundId;
    }
}
