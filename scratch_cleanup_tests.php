<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Postulante;
use App\Models\Inscripcion;
use App\Models\Voucher;

try {
    \Illuminate\Support\Facades\DB::transaction(function() {
        // Find test postulantes
        $testPostulantes = Postulante::where('email', 'like', '%@example.com')
            ->orWhere('ap_paterno', 'like', 'AptoPaterno%')
            ->orWhere('ap_paterno', 'like', 'Paterno%')
            ->get();

        echo "Encontrados " . $testPostulantes->count() . " postulantes de prueba.\n";

        if ($testPostulantes->isNotEmpty()) {
            $postulanteIds = $testPostulantes->pluck('id')->toArray();
            
            // Delete related inscripciones
            $deletedInscripciones = Inscripcion::whereIn('postulante_id', $postulanteIds)->delete();
            echo "Eliminadas {$deletedInscripciones} inscripciones de prueba.\n";
            
            // Delete related vouchers (we can use the postulants' num_iden)
            $numIdens = $testPostulantes->pluck('num_iden')->toArray();
            $deletedVouchers = Voucher::whereIn('num_iden', $numIdens)->delete();
            echo "Eliminados {$deletedVouchers} vouchers de prueba.\n";

            // Delete postulants
            $deletedPostulantes = Postulante::whereIn('id', $postulanteIds)->delete();
            echo "Eliminados {$deletedPostulantes} postulantes de prueba.\n";
        }
    });
    echo "¡Limpieza completada con éxito!\n";
} catch (\Exception $e) {
    echo "Error durante la limpieza: " . $e->getMessage() . "\n";
}
