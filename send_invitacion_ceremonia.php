<?php

use App\Mail\InvitacionCeremoniaEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Inscripcion;
use App\Services\ResultadosService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- CONFIGURACIÓN ---
// Si se define un correo aquí, se enviará una sola prueba a este correo simulating el primer postulante de la lista.
// Si se deja vacío (null o ''), se enviará a todos los primeros y segundos puestos reales.
$testEmail = '';

// Parámetros de la Ceremonia (pueden ser modificados aquí)
$fechaCeremonia = 'Sábado 11 de julio de 2026';
$horaCeremonia  = '09:00 AM';
$lugarCeremonia = 'Auditorio de la Escuela de Posgrado UNPRG';
// ---------------------

// Resolver el servicio de resultados para obtener el ranking
$resultadosService = app(ResultadosService::class);
$ranking = $resultadosService->getRankingMerito();

// Filtrar únicamente primeros y segundos puestos (merito_programa = 1 o 2)
$ganadores = $ranking->filter(function ($item) {
    return in_array((int)$item['merito_programa'], [1, 2]);
})->values();

$totalGanadores = $ganadores->count();

echo "---------------------------------------------------------\n";
echo "Campaña: Invitación a Ceremonia de Primeros y Segundos Puestos\n";
echo "Fecha de Ceremonia: {$fechaCeremonia}\n";
echo "Hora: {$horaCeremonia}\n";
echo "Lugar: {$lugarCeremonia}\n";
echo "Total de primeros y segundos puestos identificados: {$totalGanadores}\n";
echo "---------------------------------------------------------\n";

if ($totalGanadores === 0) {
    echo "[WARNING] No se encontraron postulantes calificados como 1° o 2° puesto en programas activos.\n";
    exit(0);
}

// Imprimir lista para revisión visual en consola
echo "Postulantes seleccionados:\n";
foreach ($ganadores as $index => $item) {
    $num = $index + 1;
    echo "{$num}. [{$item['merito_programa']}° Puesto] {$item['nombres']} {$item['apellidos']} | Nota: {$item['nota_final']} | {$item['grado']} en {$item['programa']} | Correo: {$item['email']}\n";
}
echo "---------------------------------------------------------\n";

if (!empty($testEmail)) {
    echo "MODO PRUEBA: Enviando correo de prueba a {$testEmail}...\n";
    
    // Obtener el primer postulante del ranking filtrado
    $primerGanador = $ganadores->first();
    $inscripcion = Inscripcion::with(['postulante', 'programa.grado'])->find($primerGanador['inscripcion_id']);
    
    if (!$inscripcion) {
        echo "[ERROR] No se pudo cargar el registro de inscripción ID {$primerGanador['inscripcion_id']}.\n";
        exit(1);
    }
    
    $nombreCompleto = "{$inscripcion->postulante->nombres} {$inscripcion->postulante->ap_paterno} {$inscripcion->postulante->ap_materno}";
    $merito = (int)$primerGanador['merito_programa'];
    
    try {
        Mail::to($testEmail)->send(new InvitacionCeremoniaEmail(
            $inscripcion, 
            $merito, 
            $fechaCeremonia, 
            $horaCeremonia, 
            $lugarCeremonia
        ));
        echo "[ÉXITO] Correo de prueba enviado a {$testEmail}.\n";
        echo "Simulando postulante: {$nombreCompleto} | Mérito: {$merito}° Puesto | Programa: {$inscripcion->programa->nombre}\n";
    } catch (\Exception $e) {
        echo "[ERROR] Falló el envío de prueba: " . $e->getMessage() . "\n";
    }
} else {
    echo "MODO REAL: Iniciando envío masivo a los {$totalGanadores} primeros y segundos puestos...\n";
    $successCount = 0;
    $failCount = 0;

    foreach ($ganadores as $item) {
        $inscripcion = Inscripcion::with(['postulante', 'programa.grado'])->find($item['inscripcion_id']);
        
        if (!$inscripcion || !$inscripcion->postulante) {
            echo "[OMITIDO] No se pudo cargar la inscripción o postulante ID {$item['inscripcion_id']}.\n";
            continue;
        }

        $emailDestino = $inscripcion->postulante->email;
        if (empty($emailDestino)) {
            echo "[OMITIDO] Inscripción ID {$inscripcion->id} sin correo electrónico asignado.\n";
            continue;
        }

        $nombreCompleto = "{$inscripcion->postulante->nombres} {$inscripcion->postulante->ap_paterno} {$inscripcion->postulante->ap_materno}";
        $merito = (int)$item['merito_programa'];

        try {
            Mail::to($emailDestino)->send(new InvitacionCeremoniaEmail(
                $inscripcion, 
                $merito, 
                $fechaCeremonia, 
                $horaCeremonia, 
                $lugarCeremonia
            ));
            echo "[ÉXITO] Correo enviado a: {$nombreCompleto} ({$emailDestino}) | Mérito: {$merito}° | Prog: {$inscripcion->programa->nombre}\n";
            $successCount++;
        } catch (\Exception $e) {
            echo "[ERROR] No se pudo enviar a: {$nombreCompleto} ({$emailDestino}). Error: " . $e->getMessage() . "\n";
            $failCount++;
        }
    }

    echo "---------------------------------------------------------\n";
    echo "Envío masivo finalizado. Éxito: {$successCount} | Errores: {$failCount}\n";
}
