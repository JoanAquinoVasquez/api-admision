<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$programas = App\Models\Programa::where('facultad_id', '!=', 11)
    ->withCount('inscripciones')
    ->get()
    ->filter(function($p) {
        return $p->inscripciones_count < 18;
    });

foreach ($programas as $p) {
    echo "ID: {$p->id} | Nombre: {$p->nombre} | Estado: {$p->estado} | Inscritos: {$p->inscripciones_count}\n";
}
