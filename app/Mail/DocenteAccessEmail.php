<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DocenteAccessEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $docente;
    public $plainPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($docente, $plainPassword)
    {
        $this->docente = $docente;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acceso habilitado - Docente Evaluador 2026-I',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.acceso-docente',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generar el PDF dinámicamente o usar la vista si está diseñada
        $pdf = Pdf::loadView('pdf.manual_docente');
        
        return [
            Attachment::fromData(fn () => $pdf->output(), 'Manual_Docente_Evaluador.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
