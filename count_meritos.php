<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resultadosService = app(App\Services\ResultadosService::class);
$ranking = $resultadosService->getRankingMerito();

$primeros = $ranking->filter(fn($item) => (int)$item['merito_programa'] === 1);
$segundos = $ranking->filter(fn($item) => (int)$item['merito_programa'] === 2);

echo "--- DETALLE POR PROGRAMA ACADÉMICO ---\n\n";

$agrupados = $ranking->groupBy('programa_id');
$num = 1;
foreach ($agrupados as $progId => $grupo) {
    $progNombre = $grupo->first()['programa'];
    $gradoNombre = $grupo->first()['grado'];
    
    $c1 = $grupo->filter(fn($i) => (int)$i['merito_programa'] === 1);
    $c2 = $grupo->filter(fn($i) => (int)$i['merito_programa'] === 2);
    
    echo "{$num}. {$gradoNombre} en {$progNombre}\n";
    
    echo "   🥇 1° Puesto: ";
    if ($c1->isEmpty()) {
        echo "Ninguno\n";
    } else {
        $nombres1 = $c1->map(fn($x) => "{$x['nombres']} {$x['apellidos']} (Nota: {$x['nota_final']})");
        echo $nombres1->implode(', ') . " [Total: " . $c1->count() . "]\n";
    }
    
    echo "   🥈 2° Puesto: ";
    if ($c2->isEmpty()) {
        echo "Ninguno\n";
    } else {
        $nombres2 = $c2->map(fn($x) => "{$x['nombres']} {$x['apellidos']} (Nota: {$x['nota_final']})");
        echo $nombres2->implode(', ') . " [Total: " . $c2->count() . "]\n";
    }
    
    echo "\n";
    $num++;
}

echo "Total Programas Evaluados con Ingresantes: " . $agrupados->count() . "\n";
echo "Total Primeros Puestos: " . $primeros->count() . "\n";
echo "Total Segundos Puestos: " . $segundos->count() . "\n";
