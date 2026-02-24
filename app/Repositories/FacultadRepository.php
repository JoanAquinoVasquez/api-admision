<?php

namespace App\Repositories;

use App\Models\Facultad;
use App\Repositories\Contracts\FacultadRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FacultadRepository extends BaseRepository implements FacultadRepositoryInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function makeModel(): Model
    {
        return new Facultad();
    }

    /**
     * Get all facultades with programs
     */
    public function getAllWithPrograms(): Collection
    {
        return $this->model->with(['programas.grado'])->get();
    }

    /**
     * Get facultad with programs and inscriptions
     */
    public function getWithProgramsAndInscriptions(int $id): ?Model
    {
        return $this->model->with(['programas.grado', 'programas.inscripciones'])
            ->find($id);
    }
}
