<?php

namespace App\Repositories;

use App\Models\Inscripcion;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class InscripcionRepository extends BaseRepository implements InscripcionRepositoryInterface
{
    /**
     * InscripcionRepository constructor.
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
        return new Inscripcion();
    }

    /**
     * Find inscripcion with all relations
     *
     * @param int $id
     * @return Model|null
     */
    public function findWithRelations(int $id): ?Model
    {
        return $this->model->with([
            'programa.grado',
            'programa.facultad',
            'postulante.documentos',
            'postulante.distrito.provincia.departamento',
            'voucher.conceptoPago'
        ])->find($id);
    }

    /**
     * Get all inscripciones with relations
     *
     * @return Collection
     */
    public function getAllWithRelations(): Collection
    {
        return $this->model->with([
            'programa.grado',
            'programa.facultad',
            'postulante.documentos',
            'postulante.distrito.provincia.departamento',
            'voucher.conceptoPago'
        ])->get();
    }

    /**
     * Get inscripciones by programa
     *
     * @param int $programaId
     * @return Collection
     */
    public function getByPrograma(int $programaId): Collection
    {
        return $this->model->where('programa_id', $programaId)
            ->with([
                'postulante',
                'programa.grado',
                'nota'
            ])
            ->get();
    }

    /**
     * Get inscripciones by estado de validacion digital
     *
     * @param int $valDigital
     * @return Collection
     */
    public function getByValidacionDigital(int $valDigital): Collection
    {
        return $this->model->where('val_digital', $valDigital)->get();
    }

    /**
     * Get inscripciones by estado de validacion fisica
     *
     * @param int $valFisico
     * @return Collection
     */
    public function getByValidacionFisica(int $valFisico): Collection
    {
        return $this->model->where('val_fisico', $valFisico)->get();
    }

    /**
     * Get count of validated inscripciones (digital)
     *
     * @return int
     */
    public function getValidatedDigitalCount(): int
    {
        return $this->model->where('val_digital', 1)->count();
    }

    /**
     * Get count of validated inscripciones (fisica)
     *
     * @return int
     */
    public function getValidatedFisicaCount(): int
    {
        return $this->model->where('val_fisico', 1)->count();
    }

    /**
     * Get inscripciones with notas
     *
     * @return Collection
     */
    public function getWithNotas(): Collection
    {
        return $this->model->with([
            'programa.grado',
            'programa.facultad',
            'postulante',
            'nota',
        ])->get();
    }

    /**
     * Get inscripciones by grado
     *
     * @param int $gradoId
     * @return Collection
     */
    public function getByGrado(int $gradoId): Collection
    {
        return $this->model->whereHas('programa', function ($query) use ($gradoId) {
            $query->where('grado_id', $gradoId);
        })->get();
    }

    /**
     * Get estadisticas de validacion
     *
     * @return array
     */
    public function getEstadisticasValidacion(): array
    {
        $inscripciones = $this->model->with([
            'programa.grado',
            'programa.facultad',
            'postulante.documentos',
            'postulante.distrito.provincia.departamento',
            'voucher.conceptoPago'
        ])->get();

        $totalInscritos = $inscripciones->count();

        // Contadores de validaciones digitales
        $valDigital0 = $inscripciones->where('val_digital', 0)->count();
        $valDigital1 = $inscripciones->where('val_digital', 1)->count();
        $valDigital2 = $inscripciones->where('val_digital', 2)->count();

        // Contadores de validaciones físicas
        $valFisico0 = $inscripciones->where('val_fisico', 0)->count();
        $valFisico1 = $inscripciones->where('val_fisico', 1)->count();

        // Contadores por grado
        $grado1 = $inscripciones->where('programa.grado_id', 1)->count();
        $grado2 = $inscripciones->where('programa.grado_id', 2)->count();
        $grado3 = $inscripciones->where('programa.grado_id', 3)->count();

        return [
            'total_inscritos' => $totalInscritos,
            'validaciones' => [
                'digital' => [
                    'pendientes' => $valDigital0 + $valDigital2,
                    'validados' => $valDigital1,
                    'porcentaje' => $totalInscritos > 0 
                        ? round(($valDigital1 / ($valDigital0 + $valDigital1 + $valDigital2)) * 100, 1) 
                        : 0.0
                ],
                'fisico' => [
                    'faltantes' => $valFisico0,
                    'recepcionados' => $valFisico1,
                    'porcentaje' => $totalInscritos > 0 
                        ? round(($valFisico1 / ($valFisico0 + $valFisico1)) * 100, 1) 
                        : 0.0
                ],
            ],
            'grados' => [
                'doc' => $grado1,
                'mae' => $grado2,
                'seg' => $grado3
            ]
        ];
    }
}
