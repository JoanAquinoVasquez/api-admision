<?php

namespace App\Repositories\Contracts;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ProgramaRepositoryInterface extends RepositoryInterface
{
    /**
     * Get active programas with relations
     *
     * @return Collection
     */
    public function getActiveWithRelations(): Collection;

    /**
     * Get programas by concepto de pago
     *
     * @param int $conceptoPagoId
     * @return Collection
     */
    public function getByConceptoPago(int $conceptoPagoId): Collection;

    /**
     * Get programas with inscripciones count
     *
     * @return Collection
     */
    public function getProgramasWithInscripciones(): Collection;

    /**
     * Get top programas by inscripciones
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopProgramas(int $limit = 10): Collection;

    /**
     * Get programas habilitados (estado = true)
     *
     * @return Collection
     */
    public function getHabilitados(): Collection;

    /**
     * Get programas by grado
     *
     * @param int $gradoId
     * @return Collection
     */
    public function getByGrado(int $gradoId): Collection;

    /**
     * Get all programs with relations
     *
     * @return Collection
     */
    public function getAllWithRelations(): Collection;

    /**
     * Get enabled programs with inscription count
     *
     * @return Collection
     */
    public function getEnabledWithInscriptionCount(): Collection;

    /**
     * Get programs by grade
     *
     * @param int $gradeId
     * @param bool $onlyEnabled
     * @return Collection
     */
    public function getByGrade(int $gradeId, bool $onlyEnabled = false): Collection;

    public function getProgramasConConteo(): Collection;

    public function getProgramById(int $id): ?Model;
}
