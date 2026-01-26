<?php

namespace App\Repositories\Contracts;

use App\Models\PreInscripcion;
use Illuminate\Database\Eloquent\Collection;

interface PreInscripcionRepositoryInterface
{
    /**
     * Get all pre-inscripciones with relationships
     */
    public function getAllWithRelations(): Collection;

    /**
     * Find pre-inscripcion by ID
     */
    public function findById(int $id): ?PreInscripcion;

    /**
     * Find pre-inscripcion by num_iden
     */
    public function findByNumIden(string $numIden): ?PreInscripcion;

    /**
     * Create new pre-inscripcion
     */
    public function create(array $data): PreInscripcion;

    /**
     * Update pre-inscripcion
     */
    public function update(int $id, array $data): ?PreInscripcion;

    /**
     * Deactivate pre-inscripcion
     */
    public function deactivate(int $id): bool;

    /**
     * Get pre-inscripciones by programa
     */
    public function getByPrograma(int $programaId): Collection;

    /**
     * Get pre-inscripciones by grado
     */
    public function getByGrado(int $gradoId): Collection;

    /**
     * Count pre-inscripciones by programa
     */
    public function countByPrograma(int $programaId): int;

    /**
     * Get pre-inscripciones with payment status
     */
    public function getAllWithPaymentStatus(): Collection;
}
