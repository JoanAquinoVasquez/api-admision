<?php

$content = file_get_contents('REPORTE POR MERITO EPG 2026I - FINAL.pdf');

// Replace non-printable characters with space
$clean = '';
for ($i = 0; $i < strlen($content); $i++) {
    $char = $content[$i];
    $ord = ord($char);
    if (($ord >= 32 && $ord <= 126) || $ord == 10 || $ord == 13 || $ord == 9) {
        $clean .= $char;
    } else {
        $clean .= ' ';
    }
}

// Write to a temporary text file so we can read it easily or search in it
file_put_contents('scratch_pdf_text.txt', $clean);

echo "Length of clean text: " . strlen($clean) . "\n";
// Let's print out text segments that look like strings
preg_match_all('/[a-zA-Z\s]{6,}/', $clean, $matches);
$strings = array_unique(array_map('trim', $matches[0]));
$strings = array_filter($strings, function($s) {
    return strlen($s) > 5 && strlen($s) < 100;
});

echo "Unique string fragments (sample of 100):\n";
print_r(array_slice($strings, 0, 100));
