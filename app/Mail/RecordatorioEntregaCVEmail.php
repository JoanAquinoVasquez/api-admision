<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioEntregaCVEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;

    /**
     * Create a new message instance.
     */
    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENTE: Culminación de Trámite Documentario y Entrega de Expediente Físico - EPG UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Asegurar que las relaciones estén cargadas en caso de encolamiento
        $this->inscripcion->loadMissing(['postulante', 'programa.grado']);

        $postulante = $this->inscripcion->postulante;
        $programa = $this->inscripcion->programa;
        $grado = $programa ? $programa->grado : null;

        $gradoId = $programa ? $programa->grado_id : null;
        $facultadId = $programa ? $programa->facultad_id : null;

        $autoridad = config("admission.autoridades.{$gradoId}", 'Director');
        $gradoRequerido = config("admission.grados_requeridos.{$gradoId}", 'Grado Académico');

        $urlDocumentos = '';
        if ($gradoId == 3) { // Segunda Especialidad
            $urlDocumentos = config("admission.url_documentos.facultades.{$facultadId}", '');
        } else {
            $urlDocumentos = config("admission.url_documentos.default");
        }

        return new Content(
            view: 'email.recordatorio-entrega-cv',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? $grado->nombre : 'Posgrado',
                'nombre_programa' => $programa ? $programa->nombre : '',
                'autoridad' => $autoridad,
                'gradoRequerido' => $gradoRequerido,
                'urlDocumentos' => $urlDocumentos,
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
