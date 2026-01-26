<?php

namespace App\Services;

use App\Models\Postulante;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PDFService
{
    /**
     * Generar constancia de postulante
     */
    public function generateConstancia(Postulante $postulante)
    {
        $inscripcion = $postulante->inscripcion;

        if (!$inscripcion || $inscripcion->val_digital != 1) {
            throw new \Exception('El postulante no se encuentra verificado digitalmente.');
        }

        $programa = $inscripcion->programa;
        $foto = $postulante->documentos->firstWhere('tipo', 'Foto');

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
            'foto' => $foto->url,
            'nombreGrado' => $programa->grado->nombre,
            'nombrePrograma' => $programa->nombre,
            'cod_voucher' => $inscripcion->codigo,
            'updated_at' => $inscripcion->updated_at,
        ];

        $pdf = Pdf::loadView('constancia', $data);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("documento-{$postulante->num_iden}.pdf");
    }

    /**
     * Generar carnets de postulantes
     */
    public function generateCarnets(array $postulanteIds)
    {
        $postulantes = Postulante::whereIn('id', $postulanteIds)
            ->with(['inscripcion', 'documentos' => function ($query) {
                $query->where('tipo', 'Foto');
            }])->get();

        if ($postulantes->isEmpty()) {
            throw new \Exception('No hay postulantes para exportar en los criterios seleccionados.');
        }

        return view('carnets', ['postulantes' => $postulantes]);
    }
}
