<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Programa;

$minInscritos = 17; // same threshold used elsewhere

// Mapping of program IDs to assigned classrooms (aulas)
$aulasAsignadas = [
    21 => 'AULA 02',
    10 => 'AULA 03',
    34 => 'AULA 04',
    33 => 'AULA 05',
    8  => 'AULA 08',
    7  => 'AULA 09',
    22 => 'AULA 10',
    29 => 'AULA 11',
    31 => 'AULA 12',
    32 => 'AULA 13', // Agroexportación (added)
    25 => 'AULA 14',
    28 => 'AULA 15',
    27 => 'AULA 16',
    24 => 'AULA 17',
];

// Mapping of program IDs to interview evaluator(s).
$evaluadoresEspeciales = [
    9  => 'DR. CARLOS ADOLFO LOAYZA RIVAS / DR. JUAN FARIAS FEIJOO',
    21 => 'DRA. JESUS ALICIA FERNANDEZ PALOMINO / DR. FREDDY HERNANDEZ RENGIFO',
    10 => 'DR. LUIS ALBERTO OTAKE OYAMA',
    32 => 'DR. VICTOR GUSTAVO HERNANDEZ JIMENEZ', // Aula 13
    34 => 'DRA. MARIA JULIA JARAMILLO CARRION',
    33 => 'DR. MARIANO AGUSTIN RAMOS GARCIA / DR. ELEAZAR RUFASTO CAMPOS',
    8  => 'DRA. MARIANELLA LAURA GARCIA AURICH',
    7  => 'DR. HAMILTON CUEVA CAMPOS',
    22 => 'DR. LEOPOLDO YZQUIERDO HERNANDEZ',
    29 => 'DR. JOSE REUPO PERICHE',
    31 => 'M.SC. JOSE CARLOS LEIVA PIEDRA',
    25 => 'DRA. MILAGROS DEL PILAR CABEZAS MARTINEZ',
    28 => 'DR. PERCY MORANTE GAMARRA',
    27 => 'DR. JUAN CARLOS GRANADOS BARRETO',
    24 => 'DRA. GLORIA PUICON CRUZALEGUI',
];

// Fetch programs that have the minimum number of inscriptions.
$programas = Programa::where('estado', 1)
    ->withCount('inscripciones')
    ->get()
    ->filter(fn($p) => $p->inscripciones_count >= $minInscritos);

echo "--- Verificación de docentes evaluadores de entrevista y aulas asignadas ---\n\n";
foreach ($programas as $prog) {
    $aula = $aulasAsignadas[$prog->id] ?? 'Por asignar';
    $evaluador = $evaluadoresEspeciales[$prog->id] ?? 'POR ASIGNAR';
    echo sprintf(
        "Programa ID %2d | %-45s | Aula: %-10s | Evaluador(es): %s\n",
        $prog->id,
        $prog->nombre,
        $aula,
        $evaluador
    );
}
?>
