<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscripcionExpedienteFisicoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $autoridad;
    public $gradoRequerido;
    public $urlDocumentos;

    public function __construct($inscripcion, $autoridad, $gradoRequerido, $urlDocumentos)
    {
        $this->inscripcion = $inscripcion;
        $this->autoridad = $autoridad;
        $this->gradoRequerido = $gradoRequerido;
        $this->urlDocumentos = $urlDocumentos;
    }

    public function build()
    {
        $postulante = $this->inscripcion->postulante;

        $data = [
            'nombres' => $postulante->nombres,
            'apellidos' => $postulante->ap_paterno . " " . $postulante->ap_materno,
            'nombreGrado' => $this->inscripcion->programa->grado->nombre,
            'nombrePrograma' => $this->inscripcion->programa->nombre,
            'updated_at' => $this->inscripcion->updated_at,
            'examen_admision' => config('admission.cronograma.examen_admision'),
            'resultados_publicacion' => \Carbon\Carbon::parse(config('admission.cronograma.fechas_control.resultados_publicacion'))->locale('es')->isoFormat('D [de] MMMM'),
            'grado_id' => $this->inscripcion->programa->grado_id,
        ];

        return $this->view('email.inscripcion-expediente-fisico')
            ->with($data)
            ->subject('Expediente Físico Validado - EPG');
    }
}
