<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecomendacionMaestriaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $programas;

    /**
     * Create a new message instance.
     */
    public function __construct($nombre, $programas)
    {
        $this->nombre = $nombre;
        $this->programas = $programas;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 ¡Impulsa tu Futuro! Estudia tu Maestría en la Escuela de Posgrado UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.recomendacion-maestria',
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
