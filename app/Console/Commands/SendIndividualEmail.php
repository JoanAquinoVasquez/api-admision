<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\RecordatorioEntregaCVEmail;
use App\Mail\CitacionCulminacionTramiteEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendIndividualEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-individual {type} {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email (cv or citacion) to specific inscription IDs (separated by commas) if they meet the criteria';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = strtolower($this->argument('type'));
        $idsInput = $this->argument('ids');
        $ids = array_filter(array_map('intval', explode(',', $idsInput)));

        if (!in_array($type, ['cv', 'citacion'])) {
            $this->error("Invalid email type. Choose 'cv' or 'citacion'.");
            return 1;
        }

        if (empty($ids)) {
            $this->error("No valid inscription IDs provided.");
            return 1;
        }

        $this->info("Processing email type '{$type}' for " . count($ids) . " ID(s)...");

        foreach ($ids as $id) {
            $this->comment("----------------------------------------");
            $this->comment("Checking Inscription ID {$id}...");

            $inscripcion = Inscripcion::with(['postulante', 'programa.grado'])->find($id);

            if (!$inscripcion) {
                $this->error("  -> Inscription ID {$id} not found.");
                continue;
            }

            $postulante = $inscripcion->postulante;
            if (!$postulante || empty($postulante->email)) {
                $this->error("  -> The applicant does not have a valid email address.");
                continue;
            }

            // Check general active status
            if (intval($inscripcion->estado) !== 1) {
                $this->error("  -> The inscription is not active (current status: {$inscripcion->estado}).");
                continue;
            }

            $programa = $inscripcion->programa;
            if (!$programa || intval($programa->estado) !== 1) {
                $this->error("  -> The program is not active/opened.");
                continue;
            }

            if ($type === 'cv') {
                // Check CV conditions: val_fisico = 0
                if (intval($inscripcion->val_fisico) !== 0) {
                    $this->error("  -> The inscription already has physical dossier validated (val_fisico: {$inscripcion->val_fisico}).");
                    continue;
                }

                $this->info("  -> Sending CV Reminder email to {$postulante->email}...");
                Mail::to($postulante->email)->send(new RecordatorioEntregaCVEmail($inscripcion));
                $this->info("  -> Email sent successfully!");

            } else if ($type === 'citacion') {
                // Check Citacion conditions: lacks exam score (whereDoesntHave nota where examen is not null)
                $hasExamScore = Inscripcion::where('id', $id)
                    ->whereHas('nota', function ($sq) {
                        $sq->whereNotNull('examen');
                    })
                    ->exists();

                if ($hasExamScore) {
                    $this->error("  -> The applicant already has an exam score loaded.");
                    continue;
                }

                $this->info("  -> Sending Saturday Citation email to {$postulante->email}...");
                Mail::to($postulante->email)->send(new CitacionCulminacionTramiteEmail($inscripcion));
                $this->info("  -> Email sent successfully!");
            }
        }

        $this->comment("----------------------------------------");
        $this->info("Finished processing all IDs.");
        return 0;
    }
}
