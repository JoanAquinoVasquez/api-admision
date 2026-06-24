<?php

namespace App\Console\Commands;

use App\Mail\AgroexportacionActualizacionEmail;
use App\Models\Inscripcion;
use App\Models\Programa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAgroexportacionUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:agroexportacion-update 
                            {--preview : Solo muestra los destinatarios sin enviar}
                            {--send : Confirma el envío de los correos}';

    /**
     * The console command description.
     */
    protected $description = 'Envía correo de actualización a inscritos del programa Agroexportación Sostenible';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Buscar el programa de Agroexportación Sostenible
        $programa = Programa::where('nombre', 'LIKE', '%Agroexportaci%Sostenible%')->first();

        if (!$programa) {
            $this->error('No se encontró el programa de Agroexportación Sostenible.');
            return 1;
        }

        $this->info("Programa encontrado: {$programa->nombre} (ID: {$programa->id})");
        $this->info("Grado: " . ($programa->grado->nombre ?? 'N/A'));

        // Obtener inscripciones del programa
        $inscripciones = Inscripcion::where('programa_id', $programa->id)
            ->with(['postulante', 'programa.grado'])
            ->get();

        if ($inscripciones->isEmpty()) {
            $this->warn('No se encontraron inscripciones para este programa.');
            return 0;
        }

        $this->info("Total inscripciones encontradas: {$inscripciones->count()}");
        $this->newLine();

        // Mostrar tabla de destinatarios
        $tableData = $inscripciones->map(function ($inscripcion) {
            return [
                'ID' => $inscripcion->id,
                'DNI' => $inscripcion->postulante->num_iden ?? 'N/A',
                'Nombres' => ($inscripcion->postulante->nombres ?? '') . ' ' . 
                             ($inscripcion->postulante->ap_paterno ?? '') . ' ' . 
                             ($inscripcion->postulante->ap_materno ?? ''),
                'Email' => $inscripcion->postulante->email ?? 'Sin email',
                'Estado Inscripción' => $inscripcion->estado ? 'Activa' : 'Inhabilitada',
            ];
        })->toArray();

        $this->table(['ID', 'DNI', 'Nombres', 'Email', 'Estado Inscripción'], $tableData);
        $this->newLine();

        // Modo preview
        if ($this->option('preview') || !$this->option('send')) {
            $this->info("🔍 Modo PREVIEW: No se enviaron correos.");
            $this->info("Para enviar los correos, ejecute: php artisan mail:agroexportacion-update --send");
            return 0;
        }

        // Modo envío
        if (!$this->confirm("¿Confirma el envío de {$inscripciones->count()} correos?")) {
            $this->info('Envío cancelado.');
            return 0;
        }

        $enviados = 0;
        $errores = 0;

        $bar = $this->output->createProgressBar($inscripciones->count());
        $bar->start();

        foreach ($inscripciones as $inscripcion) {
            try {
                $email = $inscripcion->postulante->email ?? null;

                if (!$email) {
                    $this->newLine();
                    $this->warn("⚠ Sin email para inscripción ID {$inscripcion->id} - Omitido");
                    $errores++;
                    $bar->advance();
                    continue;
                }

                Mail::to($email)->queue(new AgroexportacionActualizacionEmail($inscripcion));
                $enviados++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Error enviando a inscripción ID {$inscripcion->id}: {$e->getMessage()}");
                Log::error("Error enviando correo agroexportación update para inscripción ID {$inscripcion->id}: " . $e->getMessage());
                $errores++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Correos encolados: {$enviados}");
        if ($errores > 0) {
            $this->warn("⚠ Errores: {$errores}");
        }

        return 0;
    }
}
