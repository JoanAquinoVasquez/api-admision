<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resultadosService = app(App\Services\ResultadosService::class);
$ranking = $resultadosService->getRankingMerito();

// Filtrar 1° y 2° puestos
$ganadores = $ranking->filter(function ($item) {
    return in_array((int)$item['merito_programa'], [1, 2]);
})->values();

$data = [];
$num = 1;

foreach ($ganadores as $item) {
    $inscripcion = \App\Models\Inscripcion::with(['postulante', 'programa.grado'])->find($item['inscripcion_id']);
    
    if (!$inscripcion || !$inscripcion->postulante) {
        continue;
    }

    $postulante = $inscripcion->postulante;
    
    $data[] = [
        'num' => $num++,
        'grado' => mb_strtoupper($inscripcion->programa->grado->nombre, 'UTF-8'),
        'programa' => mb_strtoupper($inscripcion->programa->nombre, 'UTF-8'),
        'merito' => (int)$item['merito_programa'],
        'postulante' => mb_strtoupper(
            trim(preg_replace('/\s+/', ' ', "{$postulante->ap_paterno} {$postulante->ap_materno}")) . ", " . 
            trim(preg_replace('/\s+/', ' ', $postulante->nombres)),
            'UTF-8'
        ),
        'nota' => floatval($item['nota_final']),
        'email' => $postulante->email ?? '',
        'celular' => $postulante->celular ?? ''
    ];
}

$outputPath = __DIR__ . '/meritos_data.json';
file_put_contents($outputPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Datos de méritos exportados correctamente a JSON: " . realpath($outputPath) . "\n";
