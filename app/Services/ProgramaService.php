<?php

namespace App\Services;

use App\Models\Programa;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProgramaService
{
    public function __construct(
        protected ProgramaRepositoryInterface $programaRepository
    ) {
    }

    /**
     * Get all programs with relations
     */
    public function getAllWithRelations(): Collection
    {
        return $this->programaRepository->getAllWithRelations();
    }

    /**
     * Get programs optimized for landing pages
     */
    public function getForLandingPages(): \Illuminate\Support\Collection
    {
        return $this->programaRepository->getForLandingPages();
    }

    /**
     * Get enabled programs with inscription count
     */
    public function getEnabledPrograms(): Collection
    {
        return $this->programaRepository->getEnabledWithInscriptionCount();
    }

    /**
     * Get programs by grade
     */
    public function getProgramsByGrade(int $gradeId): Collection
    {
        return $this->programaRepository->getByGrade($gradeId, true); // only enabled
    }

    public function getProgramById(int $id): ?Model
    {
        return $this->programaRepository->getProgramById($id);
    }
    /**
     * Calculate pre-registration totals by grade
     */
    public function calculatePreInscripcionTotals(): array
    {
        $programas = $this->programaRepository->getProgramasConConteo();

        $totales = [];
        $totalGeneral = 0;

        foreach ($programas as $programa) {
            $gradoNombre = $programa->grado ? strtoupper(trim($programa->grado->nombre)) : 'SIN GRADO';
            $cantidad = $programa->pre_inscripciones_count;

            if (!isset($totales[$gradoNombre])) {
                $totales[$gradoNombre] = 0;
            }

            $totales[$gradoNombre] += $cantidad;
            $totalGeneral += $cantidad;
        }

        $totales['TOTAL'] = $totalGeneral;

        return $totales;
    }

    /**
     * Get list of enrolled students by program
     */
    public function getInscritosListByProgram(): Collection
    {
        $programas = $this->programaRepository->getEnabledWithInscriptionCount();

        return $programas->map(function ($programa) {
            return [
                'id' => $programa->id,
                'grado' => $programa->grado->nombre,
                'facultad_nombre' => $programa->facultad->nombre,
                'facultad_siglas' => $programa->facultad->siglas,
                'programa' => $programa->nombre,
                'vacantes' => $programa->vacantes,
                'inscritos' => $programa->inscripciones_count,
            ];
        });
    }

    /**
     * Create a new program
     */
    public function createProgram(array $data): Model
    {
        return $this->programaRepository->create($data);
    }

    /**
     * Update a program
     */
    public function updateProgram(int $id, array $data): ?Model
    {
        $programa = $this->programaRepository->find($id);

        if (!$programa) {
            return null;
        }

        $this->programaRepository->update($id, $data);

        return $this->programaRepository->find($id);
    }

    /**
     * Deactivate a program (soft delete)
     */
    public function deactivateProgram(int $id): bool
    {
        $programa = $this->programaRepository->find($id);

        if (!$programa) {
            return false;
        }

        $this->programaRepository->update($id, ['estado' => false]);
        return true;
    }

    public function getPreInscritosTotal(): array
    {
        $totales = [];
        $totalGeneral = 0;

        $programas = $this->programaRepository->getProgramasConConteo();

        foreach ($programas as $programa) {
            $nombreGrado = $programa->grado ? strtoupper(trim($programa->grado->nombre)) : 'SIN GRADO';

            $cantidad = $programa->pre_inscripciones_count;

            if (!isset($totales[$nombreGrado])) {
                $totales[$nombreGrado] = 0;
            }

            $totales[$nombreGrado] += $cantidad;
            $totalGeneral += $cantidad;
        }

        $totales['TOTAL'] = $totalGeneral;

        return $totales;
    }
}
