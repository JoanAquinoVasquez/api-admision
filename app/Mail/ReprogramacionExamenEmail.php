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

    public $postulante;

    public function __construct($postulante)
    {
        $this->postulante = $postulante;
        $this->locale('es');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NUEVA FECHA: Reprogramación del Examen de Admisión Posgrado UNPRG - 28 de Junio',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.examen-suspendido',
            with: [
                'nombre' => "{$this->postulante->nombres} {$this->postulante->ap_paterno} {$this->postulante->ap_materno}",
            ],
        );
    }

    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath(public_path('img/reprogramacion/cronograma.webp'))
                ->as('Cronograma_Admision_EPG.webp')
                ->withMime('image/webp'),
            \Illuminate\Mail\Mailables\Attachment::fromPath(public_path('img/reprogramacion/comunicado.png'))
                ->as('Comunicado_Reprogramacion_EPG.png')
                ->withMime('image/png'),
        ];
    }
}
