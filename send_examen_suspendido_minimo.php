<?php

use App\Mail\ExamenSuspendidoMinimoEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- CONFIGURACIÓN ---
// Si se define un correo aquí, se enviará una sola prueba a este correo.
// Si se deja vacío (null o ''), se enviará a TODOS los postulantes reales de los programas afectados.
$testEmail = 'jaquinov@unprg.edu.pe'; 
// ---------------------

// 1. Obtener programas aperturados (estado = 1) con menos de 14 inscritos
$programasAfectados = App\Models\Programa::where('estado', 1)
    ->withCount('inscripciones')
    ->get()
    ->filter(function($p) {
        return $p->inscripciones_count < 14;
    });

if ($programasAfectados->isEmpty()) {
    echo "No se encontraron programas aperturados con menos de 14 inscritos.\n";
    exit(0);
}

echo "Programas afectados (< 14 inscritos):\n";
foreach ($programasAfectados as $p) {
    echo " - ID: {$p->id} | {$p->nombre} ({$p->inscripciones_count} inscritos)\n";
}
echo "---------------------------------------------------------\n";

$programaIds = $programasAfectados->pluck('id')->toArray();

// 2. Obtener inscripciones activas (estado = 1) de estos programas
$inscripciones = App\Models\Inscripcion::whereIn('programa_id', $programaIds)
    ->where('estado', 1)
    ->with(['postulante', 'programa.grado'])
    ->get();

if ($inscripciones->isEmpty()) {
    echo "No se encontraron postulantes inscritos activos en los programas afectados.\n";
    exit(0);
}

if (!empty($testEmail)) {
    echo "MODO PRUEBA: Enviando correo de prueba a {$testEmail}...\n";
    // Usar la primera inscripción para la prueba
    $insPrueba = $inscripciones->first();
    try {
        Mail::to($testEmail)->send(new ExamenSuspendidoMinimoEmail($insPrueba));
        echo "[ÉXITO] Correo de prueba enviado correctamente.\n";
    } catch (\Exception $e) {
        echo "[ERROR] Falló el envío de prueba: " . $e->getMessage() . "\n";
    }
} else {
    echo "MODO REAL: Iniciando envío masivo a " . $inscripciones->count() . " postulantes...\n";
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
            Mail::to($emailDestino)->send(new ExamenSuspendidoMinimoEmail($inscripcion));
            echo "[ÉXITO] Correo enviado a: {$nombreCompleto} ({$emailDestino})\n";
            $successCount++;
        } catch (\Exception $e) {
            echo "[ERROR] No se pudo enviar a: {$nombreCompleto} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
            $failCount++;
        }
    }

    echo "---------------------------------------------------------\n";
    echo "Envío masivo finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
}
