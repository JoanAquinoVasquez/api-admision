<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\CitacionCulminacionTramiteEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCitacionCulminacionTramiteEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-citacion-culminacion-tramite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Saturday citation emails for culmination of document process to applicants with missing exam score in active programs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Inscripcion::with(['postulante', 'programa.grado'])
            ->where('estado', 1)
            ->whereHas('programa', function ($q) {
                $q->where('estado', 1);
            })
            ->where(function ($q) {
                $q->whereDoesntHave('nota')
                  ->orWhereHas('nota', fn($sq) => $sq->whereNull('examen'));
            })
            ->whereHas('postulante', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            });

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No applicants found with pending exam score in active programs.');
            return 0;
        }

        $this->info("Found {$totalCount} active inscriptions with pending exam score.");
        $this->info("Queueing citation emails...");

        $sentCount = 0;

        $query->chunk(100, function ($inscripciones) use (&$sentCount) {
            foreach ($inscripciones as $inscripcion) {
                Mail::to($inscripcion->postulante->email)->queue(new CitacionCulminacionTramiteEmail($inscripcion));
                $sentCount++;
            }
        });

        $this->info("Successfully queued {$sentCount} Saturday citation emails.");
        return 0;
    }
}
