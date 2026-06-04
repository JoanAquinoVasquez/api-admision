<?php

use App\Mail\CitacionEvaluadoresCVEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docentes = App\Models\Docente::where('tipo', 'cv')->where('estado', 1)->get();

echo "Iniciando envío de citaciones a los docentes evaluadores de CV...\n";
echo "---------------------------------------------------------------\n";

$successCount = 0;
$failCount = 0;

foreach ($docentes as $d) {
    $nombreDocente = "{$d->nombres} {$d->ap_paterno} {$d->ap_materno}";
    $emailDestino = $d->email;

    try {
        Mail::to($emailDestino)->send(new CitacionEvaluadoresCVEmail($nombreDocente));
        echo "[ÉXITO] Correo enviado a: {$nombreDocente} ({$emailDestino})\n";
        $successCount++;
    } catch (\Exception $e) {
        echo "[ERROR] No se pudo enviar a: {$nombreDocente} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

echo "---------------------------------------------------------------\n";
echo "Envío finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
