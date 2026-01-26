<?php

namespace App\Http\Controllers;

use App\Models\Correo;
use App\Models\File;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google_Client;
use Google_Service_Drive;

class CorreoController extends Controller
{
    public function __invoke()
    {
        // El contenido del archivo
        /* $fileContent = 'Hello World';

        // Obtener el ID de la carpeta desde el archivo .env
        $folderId = 'Doctorado';

        // Crear el nombre completo de la ruta, incluyendo la carpeta
        $filePath = $folderId . '/test1.txt';

        // Usar el almacenamiento de Laravel para subir el archivo a la carpeta específica
        Storage::disk('google')->put('prueba.txt', $fileContent);

        return 'File was saved to Google Drive in the specified folder'; */
        // El contenido del archivo
        $fileContent = 'Hello World!!!';

        // Usar el almacenamiento de Laravel para subir el archivo a Google Drive
        Storage::disk('google')->put('test2.txt', $fileContent);

        return 'File was saved to Google Drive';
    }

    /* public function uploadFile(Request $request)
    {
        // Validar que el archivo esté presente
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:10240', // Max 10MB
        ]);

        // Obtener el archivo
        $file = $request->file('file');

        // Subir el archivo a Google Drive usando el disco 'google'
        $filePath = $file->getClientOriginalName();
        Storage::disk('google')->put($filePath, file_get_contents($file));

        // Devolver una respuesta en formato JSON
        return response()->json([
            'message' => 'Archivo subido exitosamente a Google Drive.',
            'file' => $filePath,  // Aquí puedes devolver el nombre del archivo o cualquier otro dato relevante
            'status' => 'success',
        ], 200);
    } */

    public function listFiles()
    {
        // Obtener todos los archivos de la carpeta especificada en el archivo de configuración
        $files = Storage::disk('google')->allFiles();

        // Verificar si hay archivos
        if (empty($files)) {
            return response()->json([
                'message' => 'No hay archivos en la carpeta de Google Drive.',
                'status' => 'success',
                'files' => []
            ], 200);
        }

        // Devolver los archivos en formato JSON
        return response()->json([
            'message' => 'Archivos obtenidos exitosamente.',
            'status' => 'success',
            'files' => $files
        ], 200);
    }

    // SUBE EL ARCHIVO AL DRIVE Y DEVUELVE LA URL PERO ESTA URL SOLO DEJA DESCARGAR
    /* public function uploadFile(Request $request)
    {
        // Validar que el archivo esté presente
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:10240', // Max 10MB
        ]);

        // Obtener el archivo
        $file = $request->file('file');

        // Subir el archivo a Google Drive usando el disco 'google'
        $filePath = $file->getClientOriginalName();  // Nombre del archivo en Google Drive
        Storage::disk('google')->put($filePath, file_get_contents($file));

        // Obtener la URL del archivo usando el disco 'google'
        $fileUrl = Storage::disk('google')->url($filePath);  // Esto obtiene la URL del archivo en Google Drive

        // Guardar el enlace en la base de datos (suponiendo que tienes una tabla 'files')
        File::create([
            'nombre' => $filePath,
            'rutaFileDrive' => $fileUrl,  // Guarda la URL generada en la base de datos
        ]);

        // Devolver la respuesta
        return response()->json([
            'message' => 'Archivo subido exitosamente a Google Drive.',
            'rutaFileDrive' => $fileUrl,  // Incluye el enlace al archivo subido
            'status' => 'success',
        ], 200);
    } */

    // SUBE EL ARCHIVO AL DRIVE Y DEVUELVE LA URL, ESTA SI DEJA VISUALIZAR EL ARCHIVO DENTRO DE drive.google.com
    public function uploadFile(Request $request)
    {
        // Validar que el archivo esté presente
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:10240', // Max 10MB
        ]);

        // Obtener el archivo
        $file = $request->file('file');

        // Subir el archivo a Google Drive usando el disco 'google'
        $filePath = $file->getClientOriginalName();  // Nombre del archivo en Google Drive
        Storage::disk('google')->put($filePath, file_get_contents($file));

        // Obtener la URL del archivo usando el disco 'google'
        $fileUrl = Storage::disk('google')->url($filePath);  // Esto obtiene la URL del archivo en Google Drive

        // Extraer el ID de la URL generada
        preg_match('/\?id=([^&]*)/', $fileUrl, $matches);
        $fileId = $matches[1] ?? null;

        // Si el ID es encontrado, crear la URL en el formato deseado
        $desiredUrl = $fileId ? "https://drive.google.com/file/d/{$fileId}/view?usp=sharing" : null;

        // Guardar el enlace en la base de datos (suponiendo que tienes una tabla 'files')
        /*  File::create([
            'nombre' => $filePath,
            'rutaFileDrive' => $desiredUrl,  // Guarda la URL en el formato deseado en la base de datos
        ]); */

        // Devolver la respuesta
        return response()->json([
            'message' => 'Archivo subido exitosamente a Google Drive.',
            'rutaFileDrive' => $desiredUrl,  // Incluye el enlace al archivo subido en el formato deseado
            'status' => 'success',
        ], 200);
    }

    public function testing() {
        $vouchers = Voucher::where('concepto_pago_id', '!=', 4)->get();

        return $vouchers;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Correo $correo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Correo $correo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Correo $correo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Correo $correo)
    {
        //
    }
}
