<?php
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    echo substr($content, -3000);
} else {
    echo "Log file not found.";
}
