<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API-Only Application
|--------------------------------------------------------------------------
|
| This application functions exclusively as an API backend.
| All web routes have been disabled. API routes are defined in routes/api.php
| and are accessible at /admision-epg/api/*
|
*/

// All web routes have been disabled for API-only mode
// Email Test Routes
Route::get('/test-email-registro', function () {
    $data = [
        'nombres' => 'Juan',
        'ap_paterno' => 'Pérez',
        'ap_materno' => 'Gómez',
        'sexo' => 'Masculino',
        'email' => 'juan.perez@example.com',
        'num_iden' => '12345678',
        'celular' => '987654321',
        'direccion' => 'Calle Falsa 123'
    ];
    $nombre_grado = 'Maestría';
    $nombre_programa = 'Ingeniería de Sistemas';
    $url = 'https://example.com';

    return view('email.inscripcion-registrada', compact('data', 'nombre_grado', 'nombre_programa', 'url'));
});

Route::get('/test-email-validacion', function () {
    $inscripcion = (object)[
        'postulante' => (object)[
            'nombres' => 'Maria',
            'ap_paterno' => 'Lopez',
            'ap_materno' => 'Diaz',
            'sexo' => 'Femenino',
        ],
        'programa' => (object)[
            'nombre' => 'Gestión Pública',
            'grado' => (object)['nombre' => 'Doctorado'],
            'grado_id' => 2,
            'facultad_id' => 1
        ]
    ];
    $gradoRequerido = 'Grado de Maestro';
    $autoridad = 'Director de la EPG';
    $urlDocumentos = 'https://example.com/docs';

    return view('email.inscripcion-validada', compact('inscripcion', 'gradoRequerido', 'autoridad', 'urlDocumentos'));
});

Route::get('/test-email-fisico', function () {
    $inscripcion = (object)[
        'postulante' => (object)[
            'nombres' => 'Juan',
            'ap_paterno' => 'Pérez',
            'ap_materno' => 'Gómez',
            'sexo' => 'Masculino'
        ],
        'programa' => (object)[
            'nombre' => 'Educación con mención en Gestión Educativa',
            'grado' => (object)['nombre' => 'Maestría'],
            'grado_id' => 2,
            'facultad_id' => 1
        ]
    ];
    $gradoRequerido = 'Grado de Maestro';
    $autoridad = 'Director de la EPG';
    $urlDocumentos = 'https://example.com/docs';
    $examen_admision = 'domingo 27 de Abril';
    $resultados_publicacion = '2025-04-30';
    $grado_id = 2;

    return view('email.inscripcion-expediente-fisico', compact('inscripcion', 'gradoRequerido', 'autoridad', 'urlDocumentos', 'examen_admision', 'resultados_publicacion'));
});