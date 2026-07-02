<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inscripcion = App\Models\Inscripcion::with(['postulante', 'programa.grado'])
    ->where('estado', 1)
    ->where('val_fisico', 0)
    ->whereHas('programa', function ($q) {
        $q->where('estado', 1);
    })
    ->first();

if (!$inscripcion) {
    echo "No matching active inscription found in the database to generate previews.\n";
    exit;
}

// 1. Render Recordatorio de CV Físico
$mailable1 = new App\Mail\RecordatorioEntregaCVEmail($inscripcion);
$html1 = $mailable1->render();
file_put_contents('../preview_reminder_cv.html', $html1);
echo "CV delivery reminder preview regenerated.\n";

// 2. Render Citación de Trámite Documentario (Sábado)
$mailable2 = new App\Mail\CitacionCulminacionTramiteEmail($inscripcion);
$html2 = $mailable2->render();
file_put_contents('../preview_citacion_tramite.html', $html2);
echo "Saturday citation preview regenerated.\n";
