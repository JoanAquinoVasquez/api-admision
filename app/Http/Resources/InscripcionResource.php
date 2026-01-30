<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscripcionResource extends JsonResource
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
            'programa_id' => $this->programa_id,
            'postulante_id' => $this->postulante_id,
            'voucher_id' => $this->voucher_id,
            'codigo' => $this->codigo,
            'val_digital' => $this->val_digital,
            'val_fisico' => $this->val_fisico,
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones (cargadas condicionalmente o si ya están presentes)
            'programa' => new ProgramaResource($this->whenLoaded('programa')),
            'postulante' => new PostulanteResource($this->whenLoaded('postulante')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
            'nota' => new NotaResource($this->whenLoaded('nota')),
        ];
    }
}
