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

        // Convert local image to base64 for PDF generation
        $fotoBase64 = null;
        if ($foto && $foto->nombre_archivo) {
            try {
                // Photos are stored locally in storage/app/public/fotos/
                $fotoPath = storage_path('app/public/' . str_replace('storage/', '', $foto->nombre_archivo));

                if (file_exists($fotoPath)) {
                    $imageContent = file_get_contents($fotoPath);
                    $fotoBase64 = 'data:image/jpeg;base64,' . base64_encode($imageContent);
                } else {
                    Log::warning('Photo file not found for constancia', [
                        'postulante_id' => $postulante->id,
                        'path' => $fotoPath
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Error loading photo for constancia', [
                    'postulante_id' => $postulante->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

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
            'foto' => $fotoBase64,
            'nombreGrado' => $programa->grado->nombre,
            'nombrePrograma' => $programa->nombre,
            'cod_voucher' => $inscripcion->codigo,
            'updated_at' => $inscripcion->updated_at,
        ];

        $pdf = Pdf::loadView('constancia', $data);
        $pdf->setOption('isRemoteEnabled', false); // Disabled since we use base64
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("documento-{$postulante->num_iden}_" . now()->format('d-m-Y_His') . ".pdf");
    }

    /**
     * Generar carnets de postulantes
     */
    public function generateCarnets(array $postulanteIds)
    {
        $postulantes = Postulante::whereIn('id', $postulanteIds)
            ->with([
                'inscripcion',
                'documentos' => function ($query) {
                    $query->where('tipo', 'Foto');
                }
            ])->get();

        if ($postulantes->isEmpty()) {
            throw new \Exception('No hay postulantes para exportar en los criterios seleccionados.');
        }

        return view('carnets', ['postulantes' => $postulantes]);
    }
}
