<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionCeremoniaEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $merito;
    public $fechaCeremonia;
    public $horaCeremonia;
    public $lugarCeremonia;

    /**
     * Create a new message instance.
     *
     * @param Inscripcion $inscripcion
     * @param int $merito
     * @param string $fechaCeremonia
     * @param string $horaCeremonia
     * @param string $lugarCeremonia
     */
    public function __construct(
        Inscripcion $inscripcion,
        int $merito,
        string $fechaCeremonia = 'Sábado 11 de julio',
        string $horaCeremonia = '09:00 AM',
        string $lugarCeremonia = 'Auditorio de la Escuela de Posgrado UNPRG'
    ) {
        $this->inscripcion = $inscripcion;
        $this->merito = $merito;
        $this->fechaCeremonia = $fechaCeremonia;
        $this->horaCeremonia = $horaCeremonia;
        $this->lugarCeremonia = $lugarCeremonia;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $puestoText = (int)$this->merito === 1 ? 'Primer Puesto' : 'Segundo Puesto';
        return new Envelope(
            subject: "🏆 ¡Felicitaciones! {$puestoText} - Invitación a Ceremonia de Reconocimiento | Escuela de Posgrado UNPRG",
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

        return new Content(
            view: 'email.invitacion-ceremonia',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? mb_strtoupper($grado->nombre, 'UTF-8') : 'POSGRADO',
                'nombre_programa' => $programa ? mb_strtoupper($programa->nombre, 'UTF-8') : '',
                'merito' => $this->merito,
                'fecha_ceremonia' => $this->fechaCeremonia,
                'hora_ceremonia' => $this->horaCeremonia,
                'lugar_ceremonia' => $this->lugarCeremonia,
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
