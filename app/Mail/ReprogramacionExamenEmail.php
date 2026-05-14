<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReprogramacionExamenEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENTE: Reprogramación del Examen de Admisión - EPG UNPRG',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.examen-suspendido',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
