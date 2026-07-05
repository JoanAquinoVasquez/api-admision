<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::select("SELECT id, cv, entrevista, examen, 
                  cv REGEXP '^[0-9]+(\.[0-9]+)?$' AS cv_ok,
                  entrevista REGEXP '^[0-9]+(\.[0-9]+)?$' AS entrevista_ok,
                  examen REGEXP '^[0-9]+(\.[0-9]+)?$' AS examen_ok
                  FROM notas WHERE inscripcion_id = 762");

print_r($row);
