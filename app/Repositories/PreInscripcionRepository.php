<?php

namespace App\Repositories;

use App\Models\PreInscripcion;
use App\Models\Voucher;
use App\Repositories\Contracts\PreInscripcionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PreInscripcionRepository implements PreInscripcionRepositoryInterface
{
    /**
     * Get all pre-inscripciones with relationships
     */
    public function getAllWithRelations(): Collection
    {
        return PreInscripcion::with([
            'programa.facultad',
            'programa.grado',
            'distrito.provincia.departamento'
        ])->get();
    }

    /**
     * Find pre-inscripcion by ID
     */
    public function findById(int $id): ?PreInscripcion
    {
        return PreInscripcion::with([
            'programa.facultad',
            'programa.grado',
            'distrito.provincia.departamento'
        ])->find($id);
    }

    /**
     * Find pre-inscripcion by num_iden
     */
    public function findByNumIden(string $numIden): ?PreInscripcion
    {
        return PreInscripcion::where('num_iden', $numIden)
            ->with([
                'programa.facultad',
                'programa.grado',
                'distrito.provincia.departamento'
            ])
            ->first();
    }

    /**
     * Create new pre-inscripcion
     */
    public function create(array $data): PreInscripcion
    {
        return PreInscripcion::create($data);
    }

    /**
     * Update pre-inscripcion
     */
    public function update(int $id, array $data): ?PreInscripcion
    {
        $preInscripcion = $this->findById($id);

        if ($preInscripcion) {
            $preInscripcion->update($data);
            return $preInscripcion->fresh();
        }

        return null;
    }

    /**
     * Deactivate pre-inscripcion
     */
    public function deactivate(int $id): bool
    {
        $preInscripcion = $this->findById($id);

        if ($preInscripcion) {
            return $preInscripcion->update(['estado' => false]);
        }

        return false;
    }

    /**
     * Get pre-inscripciones by programa
     */
    public function getByPrograma(int $programaId): Collection
    {
        return PreInscripcion::where('programa_id', $programaId)
            ->with([
                'programa.facultad',
                'programa.grado',
                'distrito.provincia.departamento'
            ])
            ->get();
    }

    /**
     * Get pre-inscripciones by grado
     */
    public function getByGrado(int $gradoId): Collection
    {
        return PreInscripcion::whereHas('programa', function ($query) use ($gradoId) {
            $query->where('grado_id', $gradoId);
        })
            ->with([
                'programa.facultad',
                'programa.grado',
                'distrito.provincia.departamento'
            ])
            ->get();
    }

    /**
     * Count pre-inscripciones by programa
     */
    public function countByPrograma(int $programaId): int
    {
        return PreInscripcion::where('programa_id', $programaId)->count();
    }

    /**
     * Get pre-inscripciones with payment status
     */
    public function getAllWithPaymentStatus(): Collection
    {
        return PreInscripcion::with([
            'programa.facultad',
            'programa.grado',
        ])
            ->select('pre_inscripcions.*')
            ->selectSub(
                Voucher::selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 2 END')
                    ->whereColumn('vouchers.num_iden', 'pre_inscripcions.num_iden'),
                'pago'
            )
            ->get();
    }
}
