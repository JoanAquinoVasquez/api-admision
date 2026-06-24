<?php
$file = 'epgunpr689_db_admision2026I (2).sql';
if (!file_exists($file)) {
    echo "File not found\n";
    exit;
}

$handle = fopen($file, 'r');
$inInscripcions = false;
$sqlContent = '';

while (($line = fgets($handle)) !== false) {
    if (stripos($line, "INSERT INTO `inscripcions`") !== false) {
        $inInscripcions = true;
        $sqlContent .= $line;
    } elseif ($inInscripcions) {
        if (trim($line) === '' || strpos($line, 'INSERT INTO') === 0 || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
            $inInscripcions = false;
        } else {
            $sqlContent .= $line;
        }
    }
}
fclose($handle);

// Now parse the SQL content to extract records.
// The structure is like: INSERT INTO `inscripcions` VALUES (1, ...), (2, ...);
// Let's extract all the blocks of values.
// We can use a regex to capture everything inside parentheses.
preg_match_all('/\(([^)]+)\)/', $sqlContent, $matches);

$zeroStateIds = [];
$totalRows = 0;

// The columns are: id, postulante_id, programa_id, voucher_id, codigo, val_digital, val_fisico, observacion, estado, created_at, updated_at
// Let's verify columns count or parse them properly.
foreach ($matches[1] as $match) {
    // Split by comma, but be careful with strings that might contain commas.
    // A simple str_getcsv can parse this if we replace single quotes or format it.
    // However, since it's SQL values, let's parse using str_getcsv.
    $row = str_getcsv($match, ',', "'");
    if (count($row) >= 9) {
        $totalRows++;
        $id = trim($row[0]);
        $estado = trim($row[8]); // 9th column (index 8)
        if ($estado === '0') {
            $zeroStateIds[] = (int)$id;
        }
    }
}

echo "Total rows parsed: $totalRows\n";
echo "Rows with estado = 0: " . count($zeroStateIds) . "\n";
if (count($zeroStateIds) > 0) {
    echo "IDs to revert to 0: " . implode(', ', $zeroStateIds) . "\n";
    
    // Generate SQL update
    $sqlReset = "UPDATE inscripcions SET estado = 0 WHERE id IN (" . implode(', ', $zeroStateIds) . ");";
    echo "\nSQL TO RESTORE:\n$sqlReset\n";
}
