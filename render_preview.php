<?php

use App\Models\Inscripcion;
use App\Mail\AgroexportacionActualizacionEmail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inscripcion = Inscripcion::where('programa_id', 32)
    ->with(['postulante', 'programa.grado'])
    ->first();

if (!$inscripcion) {
    echo "No se encontró inscripción\n";
    exit(1);
}

$mailable = new AgroexportacionActualizacionEmail($inscripcion);
$html = $mailable->render();

file_put_contents(storage_path('app/preview-email.html'), $html);
echo "Preview guardado OK\n";
