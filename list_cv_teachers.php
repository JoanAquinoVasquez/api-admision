<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docentes = App\Models\Docente::where('tipo', 'cv')->where('estado', 1)->get();

foreach ($docentes as $d) {
    echo "ID: {$d->id} | {$d->ap_paterno} {$d->ap_materno}, {$d->nombres} | Email: {$d->email} | Tipo: {$d->tipo}\n";
}
