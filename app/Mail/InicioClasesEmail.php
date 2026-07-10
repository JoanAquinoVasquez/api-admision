<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InicioClasesEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;

    /**
     * Create a new message instance.
     *
     * @param Inscripcion $inscripcion
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
            subject: '📢 ¡Comunicado Oficial! Inicio de Clases - Escuela de Posgrado UNPRG',
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
        $programaNombre = $programa ? strtoupper($programa->nombre) : '';

        // Identificar si pertenece a las excepciones del 18 de julio (Veterinaria, Plagas o Doctorado de Derecho)
        $esDoctorado = str_contains($gradoNombre, 'DOCTOR');
        $esDerecho = str_contains($programaNombre, 'DERECHO');
        $esVeterinaria = str_contains($programaNombre, 'VETERINARIA') || str_contains($programaNombre, 'SALUD ANIMAL');
        $esPlagas = str_contains($programaNombre, 'PLAGAS') || str_contains($programaNombre, 'ENFERMEDADES');

        $esExcepcionJulio18 = ($esDoctorado && $esDerecho) || $esVeterinaria || $esPlagas;

        // Mapeo de aulas para las clases del 11 de julio
        $aulasMapping = [
            7  => 'Aula 20',
            8  => 'Aula 19',
            9  => 'Aula 7',
            10 => 'Aula 3',
            21 => 'Aula 26',
            24 => 'Aula 18',
            25 => 'Aula 13',
            27 => 'Aula 17',
            28 => 'Aula 10',
            32 => 'Aula 27',
            33 => 'Aula 21',
        ];
        $aula = $aulasMapping[$programa->id] ?? 'Por confirmar';

        return new Content(
            view: 'email.inicio-clases',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? mb_strtoupper($grado->nombre, 'UTF-8') : 'POSGRADO',
                'nombre_programa' => $programa ? mb_strtoupper($programa->nombre, 'UTF-8') : '',
                'es_excepcion_julio18' => $esExcepcionJulio18,
                'aula' => $aula,
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
