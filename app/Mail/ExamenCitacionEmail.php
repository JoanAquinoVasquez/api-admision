<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamenCitacionEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $aula;

    /**
     * Create a new message instance.
     *
     * @param Inscripcion $inscripcion
     * @param string $aula
     */
    public function __construct(Inscripcion $inscripcion, string $aula)
    {
        $this->inscripcion = $inscripcion;
        $this->aula = $aula;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Citación Oficial al Examen de Admisión Presencial - Escuela de Posgrado UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Cargar relaciones necesarias
        $this->inscripcion->loadMissing(['postulante', 'programa.grado']);

        $postulante = $this->inscripcion->postulante;
        $programa = $this->inscripcion->programa;
        $grado = $programa ? $programa->grado : null;

        return new Content(
            view: 'email.examen-citacion',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? $grado->nombre : 'Posgrado',
                'nombre_programa' => $programa ? $programa->nombre : '',
                'aula' => $this->aula,
                'programa_id' => $programa ? $programa->id : null,
                'val_fisica' => $this->inscripcion->val_fisica,
            ],
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
