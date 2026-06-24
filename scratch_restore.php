<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// List of program names from the report
$reportPrograms = [
    // DOCTORADO
    ['grado' => 'DOCTORADO', 'nombre' => 'TERRITORIO Y URBANISMO SOSTENIBLE'],
    ['grado' => 'DOCTORADO', 'nombre' => 'ADMINISTRACIÓN'],
    ['grado' => 'DOCTORADO', 'nombre' => 'CIENCIAS DE ENFERMERÍA'],
    ['grado' => 'DOCTORADO', 'nombre' => 'CIENCIAS DE LA INGENIERÍA MECÁNICA Y ELÉCTRICA CON MENCIÓN EN ENERGÍA'],
    ['grado' => 'DOCTORADO', 'nombre' => 'CIENCIAS AMBIENTALES'],
    
    // MAESTRIA
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS CON MENCIÓN EN GESTIÓN DE LA CALIDAD E INOCUIDAD DE ALIMENTOS'],
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS CON MENCIÓN EN INGENIERÍA DE PROCESOS INDUSTRIALES'],
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS CON MENCIÓN EN PROYECTOS DE INVERSIÓN'],
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS DE ENFERMERÍA'],
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS DE LA INGENIERÍA MECÁNICA Y ELÉCTRICA CON MENCIÓN EN ENERGÍA'],
    ['grado' => 'MAESTRIA', 'nombre' => 'GESTIÓN INTEGRADA DE LOS RECURSOS HÍDRICOS'],
    ['grado' => 'MAESTRIA', 'nombre' => 'CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN GERENCIA EDUCATIVA ESTRATÉGICA'],
    
    // SEGUNDA ESPECIALIDAD
    ['grado' => 'SEGUNDA ESPECIALIDAD PROFESIONAL', 'nombre' => 'GESTIÓN AMBIENTAL - PRESENCIAL'],
    ['grado' => 'SEGUNDA ESPECIALIDAD PROFESIONAL', 'nombre' => 'GESTIÓN AMBIENTAL - VIRTUAL'],
    ['grado' => 'SEGUNDA ESPECIALIDAD PROFESIONAL', 'nombre' => 'EDUCACIÓN AMBIENTAL INTERCULTURAL - PRESENCIAL'],
    ['grado' => 'SEGUNDA ESPECIALIDAD PROFESIONAL', 'nombre' => 'EDUCACIÓN AMBIENTAL INTERCULTURAL - VIRTUAL'],
];

echo "Buscando programas en la base de datos...\n\n";

$foundIds = [];
$notFound = [];

foreach ($reportPrograms as $p) {
    $query = DB::table('programas')
        ->select('programas.id', 'programas.nombre as prog_nombre', 'grados.nombre as grado_nombre')
        ->leftJoin('grados', 'programas.grado_id', '=', 'grados.id')
        ->where('programas.nombre', 'LIKE', '%' . $p['nombre'] . '%');
        
    if ($p['grado'] === 'DOCTORADO') {
        $query->where('grados.nombre', 'LIKE', '%DOCTORADO%');
    } elseif ($p['grado'] === 'MAESTRIA') {
        $query->where('grados.nombre', 'LIKE', '%MAESTRIA%');
    } elseif ($p['grado'] === 'SEGUNDA ESPECIALIDAD PROFESIONAL') {
        $query->where('grados.nombre', 'LIKE', '%SEGUNDA%');
    }
    
    $results = $query->get();
    
    if ($results->isEmpty()) {
        $notFound[] = $p;
    } else {
        foreach ($results as $row) {
            echo "[OK] Encontrado: ID {$row->id} | Grado: {$row->grado_nombre} | Nombre: {$row->prog_nombre}\n";
            $foundIds[] = $row->id;
        }
    }
}

if (!empty($notFound)) {
    echo "\nNo se encontraron coincidencias exactas para:\n";
    foreach ($notFound as $p) {
        echo "- [{$p['grado']}] {$p['nombre']}\n";
    }
}

echo "\nIDs encontrados: " . implode(', ', array_unique($foundIds)) . "\n";
