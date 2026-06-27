<?php

use App\Mail\ExamenCitacionEmail;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- CONFIGURACIÓN ---
// Si se define un correo aquí, se enviará una sola prueba a este correo.
// Si se deja vacío (null o ''), se enviará a TODOS los postulantes reales de los programas afectados.
$testEmail = 'jaquinov@unprg.edu.pe';

// Límite de inscritos para considerar que un programa activo rinde examen (por defecto >= 18)
$minInscritos = 18;
// ---------------------

// 1. Obtener programas aperturados (estado = 1) con el mínimo de inscritos (excluyendo la facultad FAG, excepto los programas 33 y 34)
$programasAfectados = App\Models\Programa::where('estado', 1)
    ->where(function($query) {
        $query->where('facultad_id', '!=', 11)
              ->orWhereIn('id', [33, 34]);
    })
    ->withCount('inscripciones')
    ->get()
    ->filter(function($p) use ($minInscritos) {
        return $p->inscripciones_count >= $minInscritos || in_array($p->id, [33, 34]);
    });

if ($programasAfectados->isEmpty()) {
    echo "No se encontraron programas aperturados con {$minInscritos} o más inscritos.\n";
    exit(0);
}

echo "Programas que rinden examen (>= {$minInscritos} inscritos):\n";
foreach ($programasAfectados as $p) {
    echo " - ID: {$p->id} | {$p->nombre} ({$p->inscripciones_count} inscritos)\n";
}
echo "---------------------------------------------------------\n";

$programaIds = $programasAfectados->pluck('id')->toArray();

// 2. Obtener inscripciones activas (estado = 1) de estos programas
$inscripciones = App\Models\Inscripcion::whereIn('programa_id', $programaIds)
    ->where('estado', 1)
    ->with(['postulante', 'programa.grado'])
    ->get();

if ($inscripciones->isEmpty()) {
    echo "No se encontraron postulantes inscritos activos en los programas que rinden examen.\n";
    exit(0);
}

// 3. Definir función para determinar el aula según las reglas de negocio
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
        // Obras y Construcción: del 1 al 30 en AULA 07, restantes en AULA 06 (ordenados alfabéticamente)
        static $sortedInscripcionesIds = null;
        if ($sortedInscripcionesIds === null) {
            $sortedInscripcionesIds = \App\Models\Inscripcion::where('programa_id', 9)
                ->where('estado', 1)
                ->get()
                ->sortBy(function ($i) {
                    return strtolower($i->postulante->ap_paterno ?? '') . ' ' .
                        strtolower($i->postulante->ap_materno ?? '') . ' ' .
                        strtolower($i->postulante->nombres ?? '');
                })
                ->pluck('id')
                ->toArray();
        }
        
        $index = array_search($inscripcion->id, $sortedInscripcionesIds);
        if ($index !== false && $index < 30) {
            return 'AULA 07';
        } else {
            return 'AULA 06';
        }
    }
    
    return $aulasAsignadas[$idPrograma] ?? 'Por asignar';
}

if (!empty($testEmail)) {
    echo "MODO PRUEBA: Enviando correo de prueba a {$testEmail}...\n";
    // Usar la primera inscripción para la prueba
    $insPrueba = $inscripciones->first();
    $aulaPrueba = determinarAulaParaInscripcion($insPrueba);
    try {
        Mail::to($testEmail)->send(new ExamenCitacionEmail($insPrueba, $aulaPrueba));
        echo "[ÉXITO] Correo de prueba enviado a {$testEmail}. Aula de prueba asignada: {$aulaPrueba} para el programa: {$insPrueba->programa->nombre}\n";
    } catch (\Exception $e) {
        echo "[ERROR] Falló el envío de prueba: " . $e->getMessage() . "\n";
    }
} else {
    echo "MODO REAL: Iniciando envío masivo a " . $inscripciones->count() . " postulantes...\n";
    $successCount = 0;
    $failCount = 0;

    foreach ($inscripciones as $inscripcion) {
        $postulante = $inscripcion->postulante;
        if (!$postulante || !$postulante->email) {
            echo "[OMITIDO] Inscripción ID {$inscripcion->id} sin correo electrónico.\n";
            continue;
        }

        $aula = determinarAulaParaInscripcion($inscripcion);
        $nombreCompleto = "{$postulante->nombres} {$postulante->ap_paterno} {$postulante->ap_materno}";
        $emailDestino = $postulante->email;

        try {
            Mail::to($emailDestino)->send(new ExamenCitacionEmail($inscripcion, $aula));
            echo "[ÉXITO] Correo enviado a: {$nombreCompleto} ({$emailDestino}) | Aula: {$aula}\n";
            $successCount++;
        } catch (\Exception $e) {
            echo "[ERROR] No se pudo enviar a: {$nombreCompleto} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
            $failCount++;
        }
    }

    echo "---------------------------------------------------------\n";
    echo "Envío masivo finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
}
