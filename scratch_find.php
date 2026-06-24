<?php
$file = 'epgunpr689_db_admision2026I (2).sql';
if (!file_exists($file)) {
    echo "File not found\n";
    exit;
}

$handle = fopen($file, 'r');
$lineNumber = 0;
while (($line = fgets($handle)) !== false) {
    $lineNumber++;
    if (stripos($line, 'inscrip') !== false) {
        echo "L{$lineNumber}: " . substr($line, 0, 150) . "\n";
    }
}
fclose($handle);
