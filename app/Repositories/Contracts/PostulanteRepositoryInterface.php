<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PostulanteRepositoryInterface extends RepositoryInterface
{
    /**
     * Find postulante by numero de identificacion
     *
     * @param string $numIden
     * @return Model|null
     */
    public function findByNumIden(string $numIden): ?Model;

    /**
     * Find postulante with documentos
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithDocumentos(int $id): ?Model;

    /**
     * Check if postulante has active inscripcion
     *
     * @param int $postulanteId
     * @return bool
     */
    public function hasActiveInscripcion(int $postulanteId): bool;

    /**
     * Find postulante with all relations
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithRelations(int $id): ?Model;
}
