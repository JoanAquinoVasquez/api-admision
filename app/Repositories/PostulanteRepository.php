<?php

namespace App\Repositories;

use App\Models\Postulante;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PostulanteRepository extends BaseRepository implements PostulanteRepositoryInterface
{
    /**
     * PostulanteRepository constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        return new Postulante();
    }

    /**
     * Find postulante by numero de identificacion
     *
     * @param string $numIden
     * @return Model|null
     */
    public function findByNumIden(string $numIden): ?Model
    {
        return $this->model->where('num_iden', $numIden)->first();
    }

    /**
     * Find postulante with documentos
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithDocumentos(int $id): ?Model
    {
        return $this->model->with('documentos')->find($id);
    }

    /**
     * Check if postulante has active inscripcion
     *
     * @param int $postulanteId
     * @return bool
     */
    public function hasActiveInscripcion(int $postulanteId): bool
    {
        $postulante = $this->model->with('inscripcion')->find($postulanteId);
        
        return $postulante && $postulante->inscripcion !== null;
    }

    /**
     * Find postulante with all relations
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithRelations(int $id): ?Model
    {
        return $this->model->with([
            'documentos',
            'distrito.provincia.departamento',
            'inscripcion.programa.grado',
            'preInscripcion'
        ])->find($id);
    }
}
