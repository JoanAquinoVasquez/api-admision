<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\ReportService::class);
$response = $service->generateAulasResumenPdf();

if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
    echo "SUCCESS: StreamedResponse returned.\n";
} else {
    echo "ERROR: Unexpected response type: " . get_class($response) . "\n";
}
