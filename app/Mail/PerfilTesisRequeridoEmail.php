<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PerfilTesisRequeridoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $rubricaPath;

    /**
     * Create a new message instance.
     */
    public function __construct($nombre, $rubricaPath = null)
    {
        $this->nombre = $nombre;
        $this->rubricaPath = $rubricaPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Información Importante: Perfil de Proyecto de Tesis - Proceso de Admisión EPG 2026-I',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.perfil-tesis-requerido',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->rubricaPath && file_exists($this->rubricaPath)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromPath($this->rubricaPath)
                    ->as('Rubrica_Proyecto_Tesis.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
