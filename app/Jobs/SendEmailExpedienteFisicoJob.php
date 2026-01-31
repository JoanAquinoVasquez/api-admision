<?php

namespace App\Jobs;

use App\Mail\InscripcionExpedienteFisicoEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailExpedienteFisicoJob implements ShouldQueue
{
    use Queueable;

    protected $email;
    protected $inscripcion;
    protected $autoridad;
    protected $gradoRequerido;
    protected $urlDocumentos;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $inscripcion, $autoridad, $gradoRequerido, $urlDocumentos)
    {
        $this->email = $email;
        $this->inscripcion = $inscripcion;
        $this->autoridad = $autoridad;
        $this->gradoRequerido = $gradoRequerido;
        $this->urlDocumentos = $urlDocumentos;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(
            new InscripcionExpedienteFisicoEmail(
                $this->inscripcion,
                $this->autoridad,
                $this->gradoRequerido,
                $this->urlDocumentos
            )
        );
    }
}
