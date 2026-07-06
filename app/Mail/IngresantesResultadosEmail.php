<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IngresantesResultadosEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $pdfLink;

    /**
     * Create a new message instance.
     */
    public function __construct(Inscripcion $inscripcion, string $pdfLink = 'https://drive.google.com/file/d/1ySw6QMIMXLtlhxJmPia7trm3hXsPuw2e/view?usp=sharing')
    {
        $this->inscripcion = $inscripcion;
        $this->pdfLink = $pdfLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Felicidades, alcanzaste vacante! Resultados Oficiales - Escuela de Posgrado UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->inscripcion->loadMissing(['postulante', 'programa.grado']);

        $postulante = $this->inscripcion->postulante;
        $programa = $this->inscripcion->programa;
        $grado = $programa ? $programa->grado : null;
        $gradoNombre = $grado ? strtoupper($grado->nombre) : '';

        $esMaestria = str_contains($gradoNombre, 'MAESTR');
        $esDoctorado = str_contains($gradoNombre, 'DOCTOR');
        $esSegundaEspecialidad = str_contains($gradoNombre, 'SEGUNDA') || str_contains($gradoNombre, 'ESPECIALIDAD');

        return new Content(
            view: 'email.ingresantes-resultados',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? $grado->nombre : 'Posgrado',
                'nombre_programa' => $programa ? $programa->nombre : '',
                'pdf_link' => $this->pdfLink,
                'es_maestria' => $esMaestria,
                'es_doctorado' => $esDoctorado,
                'es_segunda_especialidad' => $esSegundaEspecialidad,
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
