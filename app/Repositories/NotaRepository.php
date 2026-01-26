<?php

namespace App\Repositories;

use App\Models\Nota;
use App\Repositories\Contracts\NotaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NotaRepository extends BaseRepository implements NotaRepositoryInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function makeModel(): Model
    {
        return new Nota();
    }

    /**
     * Get notas by inscripcion
     */
    public function getByInscripcion(int $inscripcionId): ?Model
    {
        return $this->model->where('inscripcion_id', $inscripcionId)->first();
    }

    /**
     * Get notas with complete grades (cv, entrevista, examen)
     */
    public function getCompleteGrades(): Collection
    {
        return $this->model->whereNotNull('cv')
            ->whereNotNull('entrevista')
            ->whereNotNull('examen')
            ->whereRaw('cv REGEXP "^[0-9]+(\.[0-9]+)?$"')
            ->whereRaw('entrevista REGEXP "^[0-9]+(\.[0-9]+)?$"')
            ->whereRaw('examen REGEXP "^[0-9]+(\.[0-9]+)?$"')
            ->with(['inscripcion.postulante', 'inscripcion.programa.grado'])
            ->get();
    }

    /**
     * Get notas by programa
     */
    public function getByPrograma(int $programaId): Collection
    {
        return $this->model->whereHas('inscripcion', function ($query) use ($programaId) {
            $query->where('programa_id', $programaId);
        })
            ->with(['inscripcion.postulante'])
            ->get();
    }

    /**
     * Update interview grade
     */
    public function updateInterviewGrade(int $inscripcionId, float $grade): bool
    {
        $nota = $this->getByInscripcion($inscripcionId);

        if ($nota) {
            return $nota->update(['entrevista' => $grade]);
        }

        // Create if doesn't exist
        $this->model->create([
            'inscripcion_id' => $inscripcionId,
            'entrevista' => $grade,
        ]);

        return true;
    }
}
