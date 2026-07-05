<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ins = App\Models\Inscripcion::find(762);
$nota = $ins->nota;

echo "CV: " . var_export($nota->cv, true) . PHP_EOL;
echo "Entrevista: " . var_export($nota->entrevista, true) . PHP_EOL;
echo "Examen: " . var_export($nota->examen, true) . PHP_EOL;

$cv_match = DB::select("SELECT ? REGEXP '^[0-9]+(\.[0-9]+)?$' AS m", [$nota->cv])[0]->m;
$entrevista_match = DB::select("SELECT ? REGEXP '^[0-9]+(\.[0-9]+)?$' AS m", [$nota->entrevista])[0]->m;
$examen_match = DB::select("SELECT ? REGEXP '^[0-9]+(\.[0-9]+)?$' AS m", [$nota->examen])[0]->m;

echo "CV match: " . var_export($cv_match, true) . PHP_EOL;
echo "Entrevista match: " . var_export($entrevista_match, true) . PHP_EOL;
echo "Examen match: " . var_export($examen_match, true) . PHP_EOL;
