<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InscripcionValidadaEmail extends Mailable
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
            'correo' => $postulante->email,
            'tipo_doc' => $postulante->tipo_doc === 'CE' ? 'CE' : ($postulante->tipo_doc === 'PASAPORTE' ? 'PASAPORTE' : 'DNI'),
            'num_iden' => $postulante->num_iden,
            'celular' => $postulante->celular,
            'direccion' => $postulante->direccion,
            'sexo' => $postulante->sexo,
            'fecha_nacimiento' => $postulante->fecha_nacimiento,
            'departamento' => $postulante->distrito->provincia->departamento->nombre,
            'provincia' => $postulante->distrito->provincia->nombre,
            'distrito' => $postulante->distrito->nombre,
            'foto' => $postulante->documentos->firstWhere('tipo', 'Foto')?->nombre_archivo,
            'nombreGrado' => $this->inscripcion->programa->grado->nombre,
            'nombrePrograma' => $this->inscripcion->programa->nombre,
            'cod_voucher' => $this->inscripcion->codigo,
            'updated_at' => $this->inscripcion->updated_at
        ];

        // Generar PDF en memoria
        $pdf = Pdf::loadView('constancia', $data);
        $pdfContent = $pdf->output();


        return $this->view('email.inscripcion-validada')
            //->with('inscripcion', $this->inscripcion)
            ->with($data)
            ->subject('Inscripción Validada Correctamente - EPG')
            ->attachData($pdfContent, 'Constancia_Inscripcion.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
