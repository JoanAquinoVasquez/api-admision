<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\ReservaPagoProgramaNoAperturadoEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProgramaNoAperturadoReservaEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-programa-no-aperturado-reserva';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reservation notification emails to applicants with enrollment status = 0 (unopened programs)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Inscripcion::with(['postulante.documentos', 'programa.grado', 'voucher'])
            ->where('estado', 0)
            ->whereHas('postulante', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            });

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No applicants found with enrollment status = 0.');
            return 0;
        }

        $this->info("Found {$totalCount} inscriptions with status = 0.");
        $this->info("Queueing reservation notification emails...");

        $sentCount = 0;

        $query->chunk(100, function ($inscripciones) use (&$sentCount) {
            foreach ($inscripciones as $inscripcion) {
                Mail::to($inscripcion->postulante->email)->queue(new ReservaPagoProgramaNoAperturadoEmail($inscripcion));
                $sentCount++;
            }
        });

        $this->info("Successfully queued {$sentCount} reservation notification emails.");
        return 0;
    }
}
