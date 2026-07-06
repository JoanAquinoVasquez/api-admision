<?php

use App\Mail\IngresantesResultadosEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Inscripcion;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- CONFIGURACIÓN ---
// Si se define un correo aquí, se enviará una sola prueba a este correo.
// Si se deja vacío (null o ''), se enviará a los 363 ingresantes reales de maestría y doctorado.
$testEmail = '';

// El enlace oficial de Google Drive del PDF de resultados
$pdfLink = 'https://drive.google.com/file/d/1ySw6QMIMXLtlhxJmPia7trm3hXsPuw2e/view?usp=sharing';
// ---------------------

// Obtener todas las inscripciones activas de programas activos que tienen las 3 notas registradas (admitidos/ingresantes)
// Filtrado únicamente para Segunda Especialidad (grado_id = 3)
$query = Inscripcion::where('inscripcions.estado', 1)
    ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
    ->where('programas.estado', 1)
    ->whereIn('programas.grado_id', [3])
    ->whereHas('nota', function ($q) {
        $q->whereNotNull('cv')
          ->whereNotNull('entrevista')
          ->whereNotNull('examen');
    })
    ->with(['postulante', 'programa.grado'])
    ->select('inscripcions.*');

$inscripciones = $query->get();
$totalIngresantes = $inscripciones->count();

echo "---------------------------------------------------------\n";
echo "Campaña de envío de Resultados de Admisión EPG 2026-I\n";
echo "Total de ingresantes (Segunda Especialidad) identificados: {$totalIngresantes} (Deben ser 19)\n";
echo "---------------------------------------------------------\n";

if ($totalIngresantes !== 19) {
    echo "[WARNING] La cantidad de ingresantes en la base de datos es {$totalIngresantes}, pero se esperaban 19. Verifique la base de datos.\n";
}

if (!empty($testEmail)) {
    echo "MODO PRUEBA: Enviando correo de prueba a {$testEmail}...\n";
    $insPrueba = $inscripciones->first();
    if (!$insPrueba) {
        echo "[ERROR] No se encontró ninguna inscripción para realizar la prueba.\n";
        exit(1);
    }
    
    $postulante = $insPrueba->postulante;
    $nombreCompleto = "{$postulante->nombres} {$postulante->ap_paterno} {$postulante->ap_materno}";
    
    try {
        Mail::to($testEmail)->send(new IngresantesResultadosEmail($insPrueba, $pdfLink));
        echo "[ÉXITO] Correo de prueba enviado a {$testEmail}.\n";
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
            Mail::to($emailDestino)->send(new IngresantesResultadosEmail($inscripcion, $pdfLink));
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
