<?php
use App\Models\Docente;
use App\Models\Programa;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docente = Docente::updateOrCreate(
    ['email' => 'docente_cv@unprg.edu.pe'],
    [
        'nombres' => 'Juan Alberto',
        'ap_paterno' => 'Evaluador',
        'ap_materno' => 'De CV',
        'dni' => '00000001',
        'password' => Hash::make('password'),
        'tipo' => 'cv',
        'estado' => 1
    ]
);

Programa::whereIn('id', [1, 2, 3])->update(['docente_id' => $docente->id]);

echo "Docente ID: {$docente->id} creado y asignado a programas 1, 2 y 3.\n";
