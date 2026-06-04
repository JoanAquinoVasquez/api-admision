<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitacionEvaluadoresCVEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreDocente;

    /**
     * Create a new message instance.
     */
    public function __construct($nombreDocente = 'Docente Evaluador')
    {
        $this->nombreDocente = $nombreDocente;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convocatoria a Reunión de Coordinación - Evaluación de Expedientes | Admisión Posgrado UNPRG 2026-I',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.citacion-evaluadores-cv',
        );
    }
}
