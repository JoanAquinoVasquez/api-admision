<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface NotaRepositoryInterface extends RepositoryInterface
{
    /**
     * Get notas by inscripcion
     */
    public function getByInscripcion(int $inscripcionId): ?Model;

    /**
     * Get notas with complete grades (cv, entrevista, examen)
     */
    public function getCompleteGrades(): Collection;

    /**
     * Get notas by programa
     */
    public function getByPrograma(int $programaId): Collection;

    /**
     * Update interview grade
     */
    public function updateInterviewGrade(int $inscripcionId, float $grade): bool;
}
