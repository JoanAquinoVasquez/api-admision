<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inscripcion;
use App\Models\Programa;

$minInscritos = 17; // match current config

// Obtener los programas que deben rendir examen
$programas = Programa::where('estado', 1)
    ->withCount('inscripciones')
    ->get()
    ->filter(fn($p) => $p->inscripciones_count >= $minInscritos);
$programaIds = $programas->pluck('id')->toArray();

// Obtener inscripciones activas de esos programas
$inscripciones = Inscripcion::whereIn('programa_id', $programaIds)
    ->where('estado', 1)
    ->with(['postulante', 'programa'])
    ->get();

function determinarAulaParaInscripcion($inscripcion) {
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
        32 => 'AULA 13',
        25 => 'AULA 14',
        28 => 'AULA 15',
        27 => 'AULA 16',
        24 => 'AULA 17',
    ];
    $idPrograma = $inscripcion->programa_id;
    if ($idPrograma === 9) {
        static $sortedIds = null;
        if ($sortedIds === null) {
            $sortedIds = \App\Models\Inscripcion::where('programa_id', 9)
                ->where('estado', 1)
                ->orderByRaw("LOWER(CONCAT_WS(' ', postulante->ap_paterno, postulante->ap_materno, postulante->nombres))")
                ->pluck('id')
                ->toArray();
        }
        $idx = array_search($inscripcion->id, $sortedIds);
        if ($idx !== false && $idx < 30) {
            return 'AULA 07';
        }
        return 'AULA 06';
    }
    return $aulasAsignadas[$idPrograma] ?? 'Por asignar';
}

foreach ($inscripciones as $ins) {
    $aula = determinarAulaParaInscripcion($ins);
    echo "Inscripción ID {$ins->id} | Programa {$ins->programa->nombre} (ID {$ins->programa_id}) => Aula: {$aula}\n";
}
?>
