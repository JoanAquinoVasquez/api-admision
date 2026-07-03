<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ReportService;

try {
    $reportService = app(ReportService::class);
    $pdf = $reportService->generateComplementarioAsistenciaPdf();
    echo "Complementario PDF generated successfully!\n";
} catch (\Exception $e) {
    echo "Error generating Complementario PDF: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
