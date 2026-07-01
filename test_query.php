<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$export = new App\Exports\InscripcionesPersonalizadoExport(
    null, // gradoId
    null, // programaId
    '0',  // aperturado (string '0' from request)
    ['no_trajo_cv'], // notasFilter
    null  // search
);

$collection = $export->collection();
echo "Collection count with string '0' aperturado: " . $collection->count() . "\n";
