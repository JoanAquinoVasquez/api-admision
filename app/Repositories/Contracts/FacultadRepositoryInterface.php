<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface FacultadRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all facultades with programs
     */
    public function getAllWithPrograms(): Collection;

    /**
     * Get facultad with programs and inscriptions
     */
    public function getWithProgramsAndInscriptions(int $id): ?Model;
}
