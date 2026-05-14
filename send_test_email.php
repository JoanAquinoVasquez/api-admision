<?php

use App\Models\Postulante;
use App\Mail\ReprogramacionExamenEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$postulante = new Postulante();
$postulante->nombres = 'Jorge';
$postulante->ap_paterno = 'Aquino';
$postulante->ap_materno = 'Velasquez';
$postulante->email = 'jaquinov@unprg.edu.pe';

try {
    Mail::to($postulante->email)->send(new ReprogramacionExamenEmail($postulante));
    echo "Email test sent successfully to {$postulante->email}\n";
} catch (\Exception $e) {
    echo "Error sending email test: " . $e->getMessage() . "\n";
}
