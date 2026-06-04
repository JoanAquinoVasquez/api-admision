<?php

use App\Mail\CitacionEvaluadoresCVEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$retryList = [
    [
        'email' => 'frodriguez@unprg.edu.pe',
        'nombre' => 'FRANK RICHARD RODRIGUEZ CHIRINOS'
    ],
    [
        'email' => 'saznaran@unprg.edu.pe',
        'nombre' => 'SANDRA LISETTE AZNARAN GUEVARA'
    ]
];

echo "Iniciando reintento de citaciones...\n";
echo "--------------------------------------\n";

$successCount = 0;
$failCount = 0;

foreach ($retryList as $d) {
    $nombreDocente = $d['nombre'];
    $emailDestino = $d['email'];

    try {
        Mail::to($emailDestino)->send(new CitacionEvaluadoresCVEmail($nombreDocente));
        echo "[ÉXITO] Correo enviado a: {$nombreDocente} ({$emailDestino})\n";
        $successCount++;
    } catch (\Exception $e) {
        echo "[ERROR] No se pudo enviar a: {$nombreDocente} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

echo "--------------------------------------\n";
echo "Reintento finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
