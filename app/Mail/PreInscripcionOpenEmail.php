<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreInscripcionOpenEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $preInscripcion;

    /**
     * Create a new message instance.
     */
    public function __construct($preInscripcion)
    {
        $this->preInscripcion = $preInscripcion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎯 ¡Inscripciones Abiertas! Admisión 2026-I - Escuela de Posgrado UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.preinscripcion-abierta',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
