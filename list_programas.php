<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Programa;

$ids = [21,10,34,33,8,7,22,29,31,32,25,28,27,24];
foreach ($ids as $id) {
    $prog = Programa::find($id);
    if ($prog) {
        echo "ID $id => {$prog->nombre}\n";
    } else {
        echo "ID $id => NOT FOUND\n";
    }
}
?>
