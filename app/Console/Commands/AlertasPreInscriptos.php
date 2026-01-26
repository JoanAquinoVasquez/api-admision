<?php

namespace App\Console\Commands;

use App\Mail\PreInscriptosAlertasEmail;
use App\Models\PreInscripcion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AlertasPreInscriptos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:alertas-pre-inscriptos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $preinscriptos = PreInscripcion::whereNotNull('postulante_id')->get();

        // foreach ($preinscriptos as $preinscripto) {
        Mail::to('riven.0506.rf@gmail.com')->send(new PreInscriptosAlertasEmail);
        // }
    }
}
