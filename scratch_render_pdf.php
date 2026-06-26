<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $mockData = [
        [
            'aula' => 'AULA 02',
            'grado' => 'MAESTRIA',
            'programa' => 'DERECHO CON MENCIÓN EN DERECHO PENAL Y PROCESAL PENAL',
            'inscripciones' => collect([
                (object)[
                    'postulante' => (object)[
                        'num_iden' => '73849182',
                        'ap_paterno' => 'Gomez',
                        'ap_materno' => 'Castillo',
                        'nombres' => 'Carlos Eduardo'
                    ]
                ],
                (object)[
                    'postulante' => (object)[
                        'num_iden' => '72910482',
                        'ap_paterno' => 'Perez',
                        'ap_materno' => 'Rodriguez',
                        'nombres' => 'Maria Fernanda'
                    ]
                ]
            ])
        ]
    ];

    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('postulante-aptos-final-aulas', ['programasData' => $mockData]);
    $pdf->setPaper('A4', 'portrait');
    file_put_contents('../preview_aulas.pdf', $pdf->output());
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
