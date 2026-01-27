<?php

namespace App\Repositories;

use App\Models\Programa;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class ProgramaRepository extends BaseRepository implements ProgramaRepositoryInterface
{
    /**
     * ProgramaRepository constructor.
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
        return new Programa();
    }

    /**
     * Get active programas with relations
     *
     * @return Collection
     */
    public function getActiveWithRelations(): Collection
    {
        return $this->model->with(['facultad', 'grado', 'conceptoPago'])
            ->where('estado', true)
            ->get();
    }

    /**
     * Get programas by concepto de pago
     *
     * @param int $conceptoPagoId
     * @return Collection
     */
    public function getByConceptoPago(int $conceptoPagoId): Collection
    {
        return $this->model->where('concepto_pago_id', $conceptoPagoId)
            ->where('estado', true)
            ->get();
    }

    /**
     * Get programas with inscripciones count
     *
     * @return Collection
     */
    public function getProgramasWithInscripciones(): Collection
    {
        return $this->model->with(['grado', 'facultad', 'conceptoPago'])
            ->withCount([
                'inscripciones',
                'inscripciones as inscripciones_val_digital_count' => function ($query) {
                    $query->where('val_digital', 1);
                },
                'inscripciones as inscripciones_val_fisico_count' => function ($query) {
                    $query->where('val_fisico', 1);
                }
            ])
            ->get()
            ->map(function ($programa) {
                // Asignar abreviatura del grado
                $abreviatura_grado = match ($programa->grado->id) {
                    1 => 'DOC',
                    2 => 'MAE',
                    3 => 'SEG',
                    default => 'N/A'
                };

                // Calcular cobertura
                $cobertura = $programa->vacantes > 0
                    ? round(($programa->inscripciones_count / $programa->vacantes) * 100, 2)
                    : 0;

                // Calcular recaudación
                if ($programa->concepto_pago_id === 3) {
                    $recaudacion = 'S/. ' . number_format($programa->inscripciones_count * 200, 2, '.', ',');
                } else {
                    $recaudacion = 'S/. ' . number_format(
                        $programa->inscripciones_count * ($programa->conceptoPago->monto ?? 0),
                        2,
                        '.',
                        ','
                    );
                }

                // Contar validados (esto requiere cargar las inscripciones o hacer subconsultas)
                // Para mantener la optimización, idealmente usaríamos withCount con condiciones, 
                // pero Laravel withCount soporta condiciones.
                // Sin embargo, para no complicar demasiado el refactor ahora, si necesitamos validados,
                // y no los cargamos, no podemos contarlos sin hacer queries extra.
                // Si el uso de memoria es crítico, deberíamos usar withCount(['inscripciones as val_digital_count' => function...])
    
                // Dado que el código original usaba la colección, y queremos optimizar:
                // Vamos a usar loadCount con filtros para estos contadores específicos si es posible,
                // o revertir a cargar inscripciones SOLO si es necesario para estos contadores.
                // Pero espera, el código original retornaba 'validados' y 'aptos'.
                // Vamos a agregar los counts condicionales.
    
                return [
                    'id' => $programa->id,
                    'grado_programa' => $abreviatura_grado . ' - ' . $programa->nombre,
                    'facultad' => $programa->facultad->siglas,
                    'inscritos' => $programa->inscripciones_count,
                    'vacantes' => $programa->vacantes,
                    'cobertura' => $cobertura,
                    'recaudacion' => $recaudacion,
                    // Estos requieren lógica extra, por ahora los dejaremos en 0 o requerirían subconsultas.
                    // Para hacerlo bien:
                    'validados' => $programa->inscripciones_val_digital_count ?? 0,
                    'aptos' => $programa->inscripciones_val_fisico_count ?? 0,
                ];
            });
    }

    /**
     * Get top programas by inscripciones
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopProgramas(int $limit = 10): Collection
    {
        return $this->model->with(['facultad', 'grado'])
            ->withCount('inscripciones')
            ->where('estado', true)
            ->orderByDesc('inscripciones_count')
            ->limit($limit)
            ->get()
            ->map(function ($programa) {
                return (object) [
                    'facultad' => $programa->facultad ? $programa->facultad->siglas : 'N/A',
                    'grado' => $programa->grado ? $programa->grado->nombre : 'N/A',
                    'programa' => $programa->nombre,
                    'total_inscritos' => $programa->inscripciones_count,
                ];
            });
    }

    /**
     * Get programas habilitados (estado = true)
     *
     * @return Collection
     */
    public function getHabilitados(): Collection
    {
        return $this->model->where('estado', true)
            ->with(['grado', 'facultad'])
            ->get();
    }

    /**
     * Get programas by grado
     *
     * @param int $gradoId
     * @return Collection
     */
    public function getByGrado(int $gradoId): Collection
    {
        return $this->model->where('grado_id', $gradoId)
            ->with(['facultad', 'conceptoPago'])
            ->get();
    }

    /**
     * Get all programs with relations
     *
     * @return Collection
     */
    public function getAllWithRelations(): Collection
    {
        return $this->model->with(['facultad', 'grado', 'conceptoPago'])
            ->get();
    }

    /**
     * Get programs for landing pages (optimized - only necessary fields)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getForLandingPages(): \Illuminate\Support\Collection
    {
        return $this->model
            ->select(['id', 'grado_id', 'facultad_id', 'nombre', 'plan_estudio', 'brochure', 'estado'])
            ->with(['facultad:id,siglas'])
            ->where('estado', true)
            ->get()
            ->map(function ($programa) {
                return [
                    'id' => $programa->id,
                    'grado_id' => $programa->grado_id,
                    'nombre' => mb_convert_case($programa->grado->nombre, MB_CASE_TITLE, "UTF-8") . ' en ' . $programa->nombre,
                    'plan_estudio' => $programa->plan_estudio,
                    'brochure' => $programa->brochure,
                    'facultad' => [
                        'siglas' => $programa->facultad?->siglas ?? 'N/A'
                    ]
                ];
            });
    }

    /**
     * Get enabled programs with inscription count
     *
     * @return Collection
     */
    public function getEnabledWithInscriptionCount(): Collection
    {
        return $this->model->where('estado', true)
            ->with(['facultad', 'grado', 'conceptoPago'])
            ->withCount('inscripciones')
            ->get();
    }

    /**
     * Get programs by grade
     * Alias for getByGrado to satisfy interface
     *
     * @param int $gradeId
     * @param bool $onlyEnabled
     * @return Collection
     */
    public function getByGrade(int $gradeId, bool $onlyEnabled = false): Collection
    {
        $query = $this->model->where('grado_id', $gradeId)
            ->with(['facultad', 'conceptoPago']);

        if ($onlyEnabled) {
            $query->where('estado', true);
        }

        return $query->get();
    }

    public function getProgramasConConteo(): Collection
    {
        return $this->model
            ->with('grado')
            ->withCount('preInscripciones')
            ->get();
    }

    public function getProgramById(int $id): ?Model
    {
        return $this->model
            ->with(['grado', 'preInscripciones']) // Opcional: Carga relaciones si las necesitas
            ->find($id);
    }
}
