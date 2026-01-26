<?php

namespace App\Jobs;

use App\Mail\InscripcionRegistradaEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInscripcionEmailJob implements ShouldQueue
{
    use Queueable;

    protected $email;
    protected $validated;
    protected $nombre_programa;
    protected $nombre_grado;
    protected $url;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $validated, $nombre_programa, $nombre_grado, $url)
    {
        $this->email = $email;
        $this->validated = $validated;
        $this->nombre_programa = $nombre_programa;
        $this->nombre_grado = $nombre_grado;
        $this->url = $url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(
            new InscripcionRegistradaEmail(
                $this->validated,
                $this->nombre_programa,
                $this->nombre_grado,
                $this->url
            )
        );
    }
}
