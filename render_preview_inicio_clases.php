<?php

use App\Models\Inscripcion;
use App\Mail\InicioClasesEmail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Obtener una inscripción de Doctorado en Derecho para la prueba
$insDerecho = Inscripcion::with(['postulante', 'programa.grado'])
    ->whereHas('programa', function ($q) {
        $q->where('nombre', 'like', '%Derecho%');
    })
    ->whereHas('programa.grado', function ($q) {
        $q->where('nombre', 'like', '%Doctor%');
    })
    ->first();

// Si no se encuentra, obtener cualquier inscripción y simular
if (!$insDerecho) {
    $insDerecho = Inscripcion::with(['postulante', 'programa.grado'])->first();
    if ($insDerecho && $insDerecho->programa) {
        $insDerecho->programa->nombre = 'Derecho y Ciencia Política';
        $insDerecho->programa->grado->nombre = 'Doctorado';
    }
}

// 2. Obtener una inscripción general (que no sea derecho, y excluyendo FACHSE y primeros/segundos puestos)
$resultadosService = app(App\Services\ResultadosService::class);
$ranking = $resultadosService->getRankingMerito();
$meritosLookup = $ranking->pluck('merito_programa', 'inscripcion_id')->toArray();

$insGeneral = Inscripcion::with(['postulante', 'programa.grado'])
    ->whereHas('programa', function ($q) {
        $q->where('estado', 1)
          ->where('facultad_id', '!=', 8)
          ->where('nombre', 'not like', '%Derecho%')
          ->where('nombre', 'not like', '%microbio%');
    })
    ->get()
    ->filter(function ($ins) use ($meritosLookup) {
        $puesto = $meritosLookup[$ins->id] ?? null;
        return $puesto !== 1 && $puesto !== 2;
    })
    ->first();

if (!$insGeneral) {
    echo "No se encontró inscripciones en la base de datos\n";
    exit(1);
}

// Render General
$mailGeneral = new InicioClasesEmail($insGeneral);
$htmlGeneral = $mailGeneral->render();
$outputPathGeneral = dirname(__DIR__) . '/preview_inicio_clases_general.html';
file_put_contents($outputPathGeneral, $htmlGeneral);
echo "Preview General guardado OK: " . realpath($outputPathGeneral) . "\n";

// Render Derecho
$mailDerecho = new InicioClasesEmail($insDerecho);
$htmlDerecho = $mailDerecho->render();
$outputPathDerecho = dirname(__DIR__) . '/preview_inicio_clases_derecho.html';
file_put_contents($outputPathDerecho, $htmlDerecho);
echo "Preview Derecho guardado OK: " . realpath($outputPathDerecho) . "\n";
