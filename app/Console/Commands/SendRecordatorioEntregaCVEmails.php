<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\RecordatorioEntregaCVEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRecordatorioEntregaCVEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-recordatorio-entrega-cv {--exclude= : Comma-separated list of Inscription IDs to exclude}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send physical CV submission reminders to applicants with pending CV delivery in active programs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $excludeIds = [];
        if ($this->option('exclude')) {
            $excludeIds = array_map('intval', explode(',', $this->option('exclude')));
        }

        $query = Inscripcion::with(['postulante', 'programa.grado'])
            ->where('estado', 1)
            ->where('val_fisico', 0)
            ->whereHas('programa', function ($q) {
                $q->where('estado', 1);
            })
            ->whereHas('postulante', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            });

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No applicants found with pending CV delivery in active programs.');
            return 0;
        }

        $this->info("Found {$totalCount} active inscriptions with pending CV physical delivery.");
        $this->info("Queueing reminder emails...");

        $sentCount = 0;

        $query->chunk(100, function ($inscripciones) use (&$sentCount) {
            foreach ($inscripciones as $inscripcion) {
                Mail::to($inscripcion->postulante->email)->queue(new RecordatorioEntregaCVEmail($inscripcion));
                $sentCount++;
            }
        });

        $this->info("Successfully queued {$sentCount} CV delivery reminder emails.");
        return 0;
    }
}
