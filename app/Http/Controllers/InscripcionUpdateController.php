<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePostulanteRequest;
use App\Jobs\MoverDocumentosGoogleDriveJob;
use App\Jobs\UpdateDocumentosDriveJob;
use App\Models\Documento;
use App\Models\Inscripcion;
use App\Models\Programa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InscripcionUpdateController extends Controller
{
    // Método para actualizar los datos de un postulante
    public function update(UpdatePostulanteRequest $request, $id)
    {
        $inscripcion = Inscripcion::find($id);
        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        // Buscar al postulante
        $postulante = $inscripcion->postulante;
        if (!$postulante) {
            return response()->json([
                'success' => false,
                'message' => 'Postulante no encontrado'
            ], 404);
        }

        try {
            // Validación de datos
            $validated = $request->validated();

            DB::beginTransaction();

            // Guardar el valor original del programa antes de actualizarlo
            $old_programa_id = $inscripcion->programa_id;

            $oldData = $postulante->getOriginal();

            // Convertir a mayúsculas si los datos existen en la solicitud
            if (isset($validated['nombres'])) {
                $validated['nombres'] = mb_strtoupper($validated['nombres'], 'UTF-8');
            }
            if (isset($validated['ap_paterno'])) {
                $validated['ap_paterno'] = mb_strtoupper($validated['ap_paterno'], 'UTF-8');
            }
            if (isset($validated['ap_materno'])) {
                $validated['ap_materno'] = mb_strtoupper($validated['ap_materno'], 'UTF-8');
            }

            $postulante->update($validated);

            // Actualizar inscripción
            if (isset($validated['programa_id'])) {
                $old_grado = $inscripcion->programa->grado->id;
                $new_grado = $validated['programa_id'] ? Programa::find($validated['programa_id'])->grado->id : $old_grado;

                if ($old_grado != $new_grado) {
                    $filesEnviados = array_keys($request->allFiles());

                    MoverDocumentosGoogleDriveJob::dispatch(
                        $postulante->id,
                        $new_grado,
                        $filesEnviados
                    )->afterCommit();
                }

                $inscripcion->update([
                    'programa_id' => $validated['programa_id'],
                ]);

                $inscripcion->refresh();
            }

            // Ahora manejamos los archivos nuevos independientemente de si se actualizó el programa o no
            // Manejar archivos nuevos
            $fileModificado = null;

            if ($request->hasFile('rutaFoto')) {
                // Buscar el documento relacionado
                $documento = Documento::where('postulante_id', $postulante->id)
                    ->where('tipo', 'Foto')
                    ->where('estado', true)
                    ->first();

                // Obtener la ruta del archivo almacenado
                $archivoRuta = $documento->nombre_archivo;

                // Ruta completa en el almacenamiento
                $filePath = storage_path('app/public/' . $archivoRuta); // Usamos storage_path para obtener la ruta completa

                // Verificar si el archivo existe usando file_exists de PHP
                if (file_exists($filePath)) {
                    // Eliminar el archivo del storage
                    unlink($filePath); // Usamos unlink() de PHP para eliminar el archivo
                }

                // Subir el nuevo archivo
                $foto = $request->file('rutaFoto');
                // Obtener extensión del archivo
                $fileExtension = $foto->getClientOriginalExtension();
                // Generar timestamp con formato HoraMinutoSegundo_DiaMesAño
                $timestamp = Carbon::now()->format('His_dmy');
                // Construir el nombre del archivo
                $fileName = "{$postulante->num_iden}_{$timestamp}.{$fileExtension}";
                // Guardar el archivo en storage/app/public/fotos/
                $path = $foto->storeAs('fotos', $fileName, 'public');

                // Actualizar la base de datos con la nueva ruta
                $documento->update([
                    'nombre_archivo' => 'storage/fotos/' . $fileName,  // Guardar solo la ruta relativa
                    'url' => asset('storage/' . 'fotos/' . $fileName)  // URL accesible
                ]);

                if (!$fileModificado)
                    $fileModificado = 'Se cambio la Foto';
                else
                    $fileModificado .= ', Foto';
            }
            $tempPaths = [];

            foreach (
                [
                    'DocumentoIdentidad' => 'rutaDocIden',
                    'Curriculum'         => 'rutaCV',
                    'Voucher'            => 'rutaVoucher',
                ] as $tipo => $inputName
            ) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $path = $file->store('temp'); // Guarda en storage/app/temp
                    $tempPaths[] = [
                        'tipo' => $tipo,
                        'path' => $path,
                    ];

                    // Acumular mensaje de modificación
                    if (!$fileModificado) {
                        $fileModificado = "Se cambió el {$tipo}";
                    } else {
                        $fileModificado .= ", {$tipo}";
                    }
                }
            }

            DB::commit();

            if (!empty($tempPaths)) {
                UpdateDocumentosDriveJob::dispatch($postulante, $tempPaths, $inscripcion->programa->grado->id);
            }

            // 3. Verificar los cambios realizados
            $datosCambiados = $postulante->getChanges(); // Obtiene los cambios realizados en el postulante

            // 4. Registrar los cambios en el log de actividad
            $oldValues = [];
            $newValues = [];

            // Para cada campo que fue cambiado, guarda los valores antiguos y nuevos
            foreach ($datosCambiados as $key => $newValue) {
                // Eliminar el campo 'updated_at' si existe
                if ($key == 'updated_at') {
                    continue;
                }
                $oldValues[$key] = $oldData[$key];  // Valor antiguo
                $newValues[$key] = $newValue;       // Valor nuevo
            }

            // Para los datos antiguos y nuevos también eliminamos el campo 'updated_at' si está presente
            if (isset($oldValues['updated_at'])) {
                unset($oldValues['updated_at']);
            }
            if (isset($newValues['updated_at'])) {
                unset($newValues['updated_at']);
            }

            if (isset($validated['programa_id']) && $validated['programa_id'] != $old_programa_id) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($inscripcion)
                    ->withProperties([
                        'subject' => [
                            'nombres' => $inscripcion->postulante->nombres,
                            'ap_paterno' => $inscripcion->postulante->ap_paterno,
                            'ap_materno' => $inscripcion->postulante->ap_materno,
                            'num_iden' => $inscripcion->postulante->num_iden,
                            'tipo_doc' => $inscripcion->postulante->tipo_doc,
                        ],
                        'programa_old' => ['programa_id' => $old_programa_id],
                        'programa_new' => ['programa_id' => $validated['programa_id']],
                        'data_old' => $oldValues,
                        'data_new' => $newValues,
                        'archivo_modificado' => $fileModificado ? $fileModificado : null,
                    ])
                    ->log("Cambio de programa y actualización de datos del postulante");
            } else {
                // Registrar actualización general
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($inscripcion)
                    ->withProperties([
                        'subject' => [
                            'nombres' => $inscripcion->postulante->nombres,
                            'ap_paterno' => $inscripcion->postulante->ap_paterno,
                            'ap_materno' => $inscripcion->postulante->ap_materno,
                            'num_iden' => $inscripcion->postulante->num_iden,
                            'tipo_doc' => $inscripcion->postulante->tipo_doc,
                        ],
                        'data_old' => $oldValues,
                        'data_new' => $newValues,
                        'archivo_modificado' => $fileModificado ? $fileModificado : null,
                    ])
                    ->log("Datos del postulante actualizados");
            }

            return response()->json([
                'success' => true,
                'message' => 'Inscripción actualizada exitosamente',
                'data' => [
                    'postulante' => $postulante,
                    'inscripcion' => $inscripcion,
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la inscripción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Mover archivos de postulante
    public function moverDocumentos($id_postulante, $idGradoNew, $request)
    {
        // Definir las carpetas de los grados
        $carpetasGrados = [
            1 => 'DOCTORADO',
            2 => 'MAESTRIA',
            3 => 'SEGUNDA-ESPECIALIDAD',
        ];

        // Definir las subcarpetas para cada tipo de documento
        $tiposDocumentos = [
            'Voucher' => 'Voucher',
            'Curriculum' => 'Curriculum',
            'DocumentoIdentidad' => 'DocumentoIdentidad',
        ];

        // Obtener los documentos del postulante
        $documentos = Documento::where('postulante_id', $id_postulante)
            ->where('estado', true)
            ->where('tipo', "!=", "Foto") // No mover la foto
            ->get();

        $tipoDocumentosMapeados = [
            'Voucher' => 'rutaVoucher',
            'Curriculum' => 'rutaCV',
            'DocumentoIdentidad' => 'rutaDocIden',
        ];

        // Mover los documentos al nuevo grado
        foreach ($documentos as $documento) {
            // Verificar si el documento no ha sido enviado en la solicitud
            if (!in_array($tipoDocumentosMapeados[$documento->tipo] ?? '', array_keys($request->allFiles()))) {
                $fileId = $this->extractGoogleDriveFileId($documento->url);

                // Obtener las carpetas de acuerdo al nuevo grado y el tipo de documento
                $carpetaGradoNuevo = $carpetasGrados[$idGradoNew] ?? 'default';
                $carpetaTipoDocumento = $tiposDocumentos[$documento->tipo] ?? 'default';

                // Componer la ruta completa para la subcarpeta
                $rutaNuevaCarpeta = "{$carpetaGradoNuevo}/{$carpetaTipoDocumento}";

                // Obtener el nombre de archivo original
                $fileName = $documento->nombre_archivo;
                // Crear la nueva ruta del archivo (solo cambiando la carpeta)
                $newFileName = preg_replace('/^[^\/]+/', $carpetaGradoNuevo, $fileName);

                $documento->update([
                    'nombre_archivo' => $newFileName
                ]);

                // Mover el archivo al nuevo grado y subcarpeta
                $this->moverFile($fileId, $rutaNuevaCarpeta);
            }
        }
    }

    /**
     * Sube los archivos a Drive y retorna la url
     */
    private function uploadToGoogleDrive($file, $id_grado, $fileType, $num_iden)
    {
        // Define los nombres de las carpetas basados en el grado
        if ($id_grado == 1) {
            $folder = 'DOCTORADO';
        } else if ($id_grado == 2) {
            $folder = 'MAESTRIA';
        } else {
            $folder = 'SEGUNDA-ESPECIALIDAD';
        }

        // Define las subcarpetas (Foto, Curriculum, DocumentoIdentidad)
        $subFolderName = match ($fileType) {
            'Voucher' => 'Voucher',
            'Curriculum' => 'Curriculum',
            'DocumentoIdentidad' => 'DocumentoIdentidad',
            default => '',
        };

        // Crear un nombre único para el archivo con el formato: num_iden_HHMMSS_ddMMyy.extension
        $timestamp = Carbon::now()->format('His_dmy');
        $fileExtension = $file->getClientOriginalExtension(); // Obtener la extensión del archivo
        $fileName = "{$num_iden}_{$timestamp}.{$fileExtension}"; // Crear el nombre del archivo

        // Subir el archivo a Google Drive usando el disco 'google'
        $filePath = $folder . '/' . $subFolderName . '/' . $fileName;  // Nombre del archivo en Google Drive
        Storage::disk('google')->put($filePath, file_get_contents($file));

        // Obtener la URL del archivo usando el disco 'google'
        $fileUrl = Storage::disk('google')->url($filePath);  // Esto obtiene la URL del archivo en Google Drive

        // Extraer el ID de la URL generada
        preg_match('/\?id=([^&]*)/', $fileUrl, $matches);
        $fileId = $matches[1] ?? null;

        // Si el ID es encontrado, crear la URL en el formato deseado
        return [
            'url' => $fileId ? "https://drive.google.com/file/d/{$fileId}/view?usp=sharing" : null,
            'fileName' => $filePath,
        ];
    }

    /**
     * Elimina un archivo del Google Drive
     */
    private function deleteFromGoogleDrive($filePath)
    {
        // Verifica si el archivo existe en Google Drive
        if (Storage::disk('google')->exists($filePath)) {
            // Elimina el archivo
            Storage::disk('google')->delete($filePath);
            return true;
        }
        return false;
    }

    /**
     * Eliminar y subir un nuevo archivo
     */
    private function handleNewFile($postulante, $fileType, $file)
    {
        // Eliminar el archivo antiguo si existe
        $documento = Documento::where('postulante_id', $postulante->id)
            ->where('tipo', $fileType)
            ->where('tipo', '!=', 'Foto')
            ->where('estado', true)
            ->first();

        if ($documento) {
            // Eliminar el archivo anterior de Google Drive
            $this->deleteFromGoogleDrive($documento->nombre_archivo);
        }

        // Subir el nuevo archivo a Google Drive
        $fileData = $this->uploadToGoogleDrive($file, $postulante->inscripcion->programa->grado->id, $fileType, $postulante->num_iden);

        // Guardar el nuevo archivo en la base de datos
        $documento->update([
            'nombre_archivo' => $fileData['fileName'],
            'url' => $fileData['url'],
        ]);
    }

    /**
     * Mueve un archivo de una carpeta a otra en Google Drive
     */
    public function moverFile($fileId, $rutaAMover)
    {
        $name_folder = env('GOOGLE_DRIVE_FOLDER', 'ProcesoAdmision2025-I');
        $service = Storage::disk('google')->getAdapter()->getService();
        $newFolderId = $this->getFolderIdByPath("$name_folder/" . $rutaAMover, $service);

        // Obtener carpeta actual del archivo
        $file = $service->files->get($fileId, ['fields' => 'parents']);
        $previousParents = join(',', $file->parents);

        $driveFile = new DriveFile();
        $updatedFile = $service->files->update(
            $fileId,
            $driveFile,
            [
                'addParents' => $newFolderId,
                'removeParents' => $previousParents, // Eliminar de la carpeta anterior
                'fields' => 'id, parents'
            ]
        );

        return response()->json([
            'message' => 'Archivo movido correctamente',
            'file' => $updatedFile
        ]);
    }

    /**
     * Obtener el ID de una carpeta en Google Drive por su ruta.
     */
    private function getFolderIdByPath($folderPath, $service)
    {
        $folders = explode('/', $folderPath);
        $parentId = 'root';
        $foundId = null;

        foreach ($folders as $folderName) {
            $query = "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '$parentId' in parents";
            $results = $service->files->listFiles(['q' => $query, 'fields' => 'files(id, name)', 'spaces' => 'drive']);

            if ($results->getFiles() == 0) {
                return null;
            }

            $foundId = $results->getFiles()[0]->getId();
            $parentId = $foundId;
        }

        return $foundId;
    }

    /**
     * Extraer el ID del archivo de una URL de Google Drive.
     */
    private function extractGoogleDriveFileId($url)
    {
        preg_match('/\/d\/(.*?)\//', $url, $matches);
        $fileId = $matches[1] ?? null;

        return $fileId;
    }
}
