<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Mail\PerfilTesisRequeridoEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendThesisProfileNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-thesis-profile-notification 
                            {--limit= : Limit the number of emails to send for testing}
                            {--test-email= : Send all emails to this address for testing}
                            {--rubrica= : Path to the rubric PDF file to attach}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a notification to Master applicants about the thesis profile requirement with indications and rubric attachment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rubricaPath = $this->option('rubrica');
        if ($rubricaPath && !file_exists($rubricaPath)) {
            $this->error("Rubric file not found at: {$rubricaPath}");
            return;
        }

        $this->info("Fetching Master applicants (grado_id = 2)...");

        $query = Inscripcion::with(['postulante', 'programa'])
            ->whereHas('programa', function ($q) {
                $q->where('grado_id', 2); // MAESTRIA
            })
            ->where('estado', '!=', 'cancelado');

        $limit = $this->option('limit');
        if ($limit) {
            $query->limit((int)$limit);
        }

        $inscripciones = $query->get();

        if ($inscripciones->isEmpty()) {
            $this->error("No Master applicants found.");
            return;
        }

        $total = $inscripciones->count();
        $this->info("Starting to send {$total} emails...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($inscripciones as $inscripcion) {
            $postulante = $inscripcion->postulante;
            $nombreCompleto = mb_convert_case("{$postulante->nombres} {$postulante->ap_paterno} {$postulante->ap_materno}", MB_CASE_UPPER, "UTF-8");
            
            $email = $this->option('test-email') ?? $postulante->email;

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new PerfilTesisRequeridoEmail($nombreCompleto, $rubricaPath));
                } catch (\Exception $e) {
                    $this->error("\nFailed to send to {$email}: {$e->getMessage()}");
                }
            } else {
                $this->warn("\nInvalid email: {$email} for {$nombreCompleto}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nDone!");
    }
}
