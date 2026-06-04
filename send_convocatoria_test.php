<?php

use App\Mail\CitacionEvaluadoresCVEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emailDestino = 'jaquinov@unprg.edu.pe';
$nombreDocente = 'Jorge Aquino Velasquez';

try {
    Mail::to($emailDestino)->send(new CitacionEvaluadoresCVEmail($nombreDocente));
    echo "Convocatoria test email sent successfully to {$emailDestino}\n";
} catch (\Exception $e) {
    echo "Error sending convocatoria test email: " . $e->getMessage() . "\n";
}
