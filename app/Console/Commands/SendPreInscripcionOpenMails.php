<?php

namespace App\Console\Commands;

use App\Mail\PreInscripcionOpenEmail;
use App\Models\PreInscripcion;
use App\Models\Programa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPreInscripcionOpenMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-preinscripcion-open {--test= : Send a test email to this address} {--all : Send emails to all pre-registered users}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send open registration notifications to pre-registered users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testEmail = $this->option('test');
        $sendAll = $this->option('all');

        if (!$testEmail && !$sendAll) {
            $this->error('You must specify either --test={email} or --all');
            return;
        }

        if ($testEmail) {
            $this->info("Sending test email to: $testEmail");

            // Get a random pre-inscription to use as data
            $preInscripcion = PreInscripcion::with(['programa.grado'])->first();

            if (!$preInscripcion) {
                // Mock one if none exists
                $preInscripcion = new PreInscripcion();
                $preInscripcion->nombres = 'Postulante';
                $preInscripcion->ap_paterno = 'de Prueba';
                $preInscripcion->email = $testEmail;

                $programa = new Programa();
                $programa->nombre = 'Maestría en Ingeniería de Sistemas';
                $programa->brochure = 'https://epgunprg.edu.pe/admision-epg/brochure-test.pdf';

                $preInscripcion->setRelation('programa', $programa);
            } else {
                // Ensure the email is the test one
                $preInscripcion->email = $testEmail;
            }

            Mail::to($testEmail)->send(new PreInscripcionOpenEmail($preInscripcion));

            $this->info('Test email sent successfully!');
            return;
        }

        if ($sendAll) {
            $preInscriptos = PreInscripcion::with(['programa.grado'])->whereNotNull('email')->get();

            if ($preInscriptos->isEmpty()) {
                $this->warn('No pre-registered users found.');
                return;
            }

            $count = $preInscriptos->count();
            if (!$this->confirm("Are you sure you want to send emails to $count pre-registered users?")) {
                $this->info('Operation cancelled.');
                return;
            }

            $this->output->progressStart($count);

            foreach ($preInscriptos as $preInscripcion) {
                try {
                    Mail::to($preInscripcion->email)->send(new PreInscripcionOpenEmail($preInscripcion));
                } catch (\Exception $e) {
                    $this->error("\nFailed to send email to $preInscripcion->email: " . $e->getMessage());
                }
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->info('All emails have been sent.');
        }
    }
}
