<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface InscripcionRepositoryInterface extends RepositoryInterface
{
    /**
     * Find inscripcion with all relations
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithRelations(int $id): ?Model;

    /**
     * Get inscripciones by programa
     *
     * @param int $programaId
     * @return Collection
     */
    public function getByPrograma(int $programaId): Collection;

    /**
     * Get inscripciones by estado de validacion digital
     *
     * @param int $valDigital
     * @return Collection
     */
    public function getByValidacionDigital(int $valDigital): Collection;

    /**
     * Get inscripciones by estado de validacion fisica
     *
     * @param int $valFisico
     * @return Collection
     */
    public function getByValidacionFisica(int $valFisico): Collection;

    /**
     * Get count of validated inscripciones (digital)
     *
     * @return int
     */
    public function getValidatedDigitalCount(): int;

    /**
     * Get count of validated inscripciones (fisica)
     *
     * @return int
     */
    public function getValidatedFisicaCount(): int;

    /**
     * Get inscripciones with notas
     *
     * @return Collection
     */
    public function getWithNotas(): Collection;

    /**
     * Get inscripciones by grado
     *
     * @param int $gradoId
     * @return Collection
     */
    public function getByGrado(int $gradoId): Collection;

    /**
     * Get estadisticas de validacion
     *
     * @return array
     */
    public function getEstadisticasValidacion(): array;

    /**
     * Get all inscripciones with relations
     *
     * @return Collection
     */
    public function getAllWithRelations(): Collection;
}
