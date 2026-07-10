<?php

use App\Mail\InicioClasesEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Inscripcion;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- CONFIGURACIÓN ---
// Si se define un correo aquí, se enviará una sola prueba a este correo simulating el primer postulante.
// Si se deja vacío (null o ''), se enviará a TODOS los ingresantes reales.
$testEmail = '';
// ---------------------

// Instanciar el servicio de resultados para obtener el ranking de méritos
$resultadosService = app(App\Services\ResultadosService::class);
$ranking = $resultadosService->getRankingMerito();
$meritosLookup = $ranking->pluck('merito_programa', 'inscripcion_id')->toArray();

// Obtener todas las inscripciones admitidas (ingresantes)
// estado = 1 (admitido/ingresante activo), programa activo, y las 3 notas registradas
// Se excluye la Facultad FACHSE (ID = 8) que contiene sus Maestrías y el Doctorado en Ciencias de la Educación
$inscripciones = Inscripcion::where('inscripcions.estado', 1)
    ->whereHas('programa', function ($q) {
        $q->where('estado', 1)
          ->where('facultad_id', '!=', 8)
          ->where('nombre', 'not like', '%microbio%');
    })
    ->whereHas('nota', function ($q) {
        $q->whereNotNull('cv')
          ->whereNotNull('entrevista')
          ->whereNotNull('examen');
    })
    ->with(['postulante', 'programa.grado'])
    ->get();

// Excluir de manera precisa a los Primeros y Segundos puestos de cada programa
$inscripciones = $inscripciones->filter(function ($inscripcion) use ($meritosLookup) {
    $puesto = $meritosLookup[$inscripcion->id] ?? null;
    return $puesto !== 1 && $puesto !== 2;
});

$totalIngresantes = $inscripciones->count();

echo "---------------------------------------------------------\n";
echo "Campaña: Notificación de Inicio de Clases EPG 2026-I\n";
echo "Total de ingresantes identificados en la base de datos: {$totalIngresantes}\n";
echo "---------------------------------------------------------\n";

if ($totalIngresantes === 0) {
    echo "[WARNING] No se encontraron ingresantes admitidos en la base de datos.\n";
    exit(0);
}

if (!empty($testEmail)) {
    echo "MODO PRUEBA: Enviando correo de prueba a {$testEmail}...\n";
    
    // Tomamos el primer ingresante general
    $insPrueba = $inscripciones->first();
    $postulante = $insPrueba->postulante;
    $nombreCompleto = "{$postulante->nombres} {$postulante->ap_paterno} {$postulante->ap_materno}";
    
    try {
        Mail::to($testEmail)->send(new InicioClasesEmail($insPrueba));
        echo "[ÉXITO] Correo de prueba (General) enviado a {$testEmail}.\n";
        echo "Simulando ingresante: {$nombreCompleto} | Programa: {$insPrueba->programa->nombre}\n";
    } catch (\Exception $e) {
        echo "[ERROR] Falló el envío de prueba: " . $e->getMessage() . "\n";
    }
} else {
    echo "MODO REAL: Iniciando envío masivo a los {$totalIngresantes} ingresantes...\n";
    $successCount = 0;
    $failCount = 0;

    foreach ($inscripciones as $inscripcion) {
        $postulante = $inscripcion->postulante;
        if (!$postulante || !$postulante->email) {
            echo "[OMITIDO] Inscripción ID {$inscripcion->id} sin correo electrónico.\n";
            continue;
        }

        $nombreCompleto = "{$postulante->nombres} {$postulante->ap_paterno} {$postulante->ap_materno}";
        $emailDestino = $postulante->email;

        try {
            Mail::to($emailDestino)->send(new InicioClasesEmail($inscripcion));
            echo "[ÉXITO] Correo enviado a: {$nombreCompleto} ({$emailDestino}) | Programa: {$inscripcion->programa->nombre}\n";
            $successCount++;
        } catch (\Exception $e) {
            echo "[ERROR] No se pudo enviar a: {$nombreCompleto} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
            $failCount++;
        }
    }

    echo "---------------------------------------------------------\n";
    echo "Envío masivo finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
}
