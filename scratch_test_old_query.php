<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ins = App\Models\Inscripcion::whereHas('nota', function ($q) {
    $q->whereNotNull('cv')
      ->whereNotNull('entrevista')
      ->whereNotNull('examen')
      ->whereRaw('cv REGEXP "^[0-9]+(\.[0-9]+)?$"')
      ->whereRaw('entrevista REGEXP "^[0-9]+(\.[0-9]+)?$"')
      ->whereRaw('examen REGEXP "^[0-9]+(\.[0-9]+)?$"');
})->pluck('id')->toArray();

echo "Old query returned: " . implode(', ', $ins) . PHP_EOL;

if (in_array(762, $ins)) {
    echo "762 is PRESENT in old query!" . PHP_EOL;
} else {
    echo "762 is ABSENT in old query!" . PHP_EOL;
}
