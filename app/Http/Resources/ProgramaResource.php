<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'vacantes' => $this->vacantes,
            'estado' => $this->estado,
            'plan_estudio' => $this->plan_estudio,
            'brochure' => $this->brochure,

            'grado_id' => $this->grado_id,
            'facultad_id' => $this->facultad_id,
            'concepto_pago_id' => $this->concepto_pago_id,
            'docente_id' => $this->docente_id,

            // Simple relations loaded
            'grado' => $this->whenLoaded('grado'),
            'facultad' => $this->whenLoaded('facultad'),
            'concepto_pago' => $this->whenLoaded('conceptoPago'),

            // Counts (if loaded)
            'inscripciones_count' => $this->whenCounted('inscripciones'),
        ];
    }
}
