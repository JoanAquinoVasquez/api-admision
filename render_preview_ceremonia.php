<?php

use App\Models\Inscripcion;
use App\Mail\InvitacionCeremoniaEmail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Obtener cualquier inscripción válida para renderizar
$inscripcion = Inscripcion::with(['postulante', 'programa.grado'])->first();

if (!$inscripcion) {
    echo "No se encontró ninguna inscripción en la base de datos\n";
    exit(1);
}

// Simulamos con el primer puesto
$mailable = new InvitacionCeremoniaEmail(
    $inscripcion, 
    1, // 1er puesto
    'Sábado 11 de julio de 2026',
    '09:00 AM',
    'Auditorio de la Escuela de Posgrado UNPRG'
);

$html = $mailable->render();

// Guardar en la raíz de la aplicación para poder abrirlo
$outputPath = dirname(__DIR__) . '/preview_invitacion_ceremonia.html';
file_put_contents($outputPath, $html);

echo "Preview guardado exitosamente en: " . realpath($outputPath) . "\n";
