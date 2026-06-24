<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\RecordatorioExpedienteEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpedienteReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-expediente-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send physical dossier submission reminders to applicants with pending validation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Inscripcion::with(['postulante', 'programa.grado'])
            ->where('estado', 1)
            ->where('val_fisico', 0)
            ->whereHas('postulante', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->whereHas('programa', function ($q) {
                $q->where('estado', 1);
            });

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No applicants found with pending physical validation.');
            return 0;
        }

        $this->info("Found {$totalCount} active inscriptions with pending physical validation.");
        $this->info("Queueing reminder emails...");

        $sentCount = 0;

        $query->chunk(100, function ($inscripciones) use (&$sentCount) {
            foreach ($inscripciones as $inscripcion) {
                Mail::to($inscripcion->postulante->email)->queue(new RecordatorioExpedienteEmail($inscripcion));
                $sentCount++;
            }
        });

        $this->info("Successfully queued {$sentCount} reminder emails.");
        return 0;
    }
}
