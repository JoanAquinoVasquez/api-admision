<?php

namespace App\Services;

use App\Models\ComisionAdmision;
use App\Models\PreInscripcion;
use App\Models\Programa;
use App\Models\Voucher;
use App\Repositories\Contracts\PreInscripcionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PreInscripcionStatisticsService
{
    public function __construct(
        protected PreInscripcionRepositoryInterface $repository
    ) {
    }

    /**
     * Get summary of pre-inscripciones
     */
    public function getResumen(): array
    {
        $totalGeneral = PreInscripcion::count();
        $totalPre_Inscritos = PreInscripcion::whereNotNull('postulante_id')->count();

        $preInscritosPagados = PreInscripcion::whereIn('num_iden', function($query) {
            $query->select('num_iden')->from('vouchers');
        })->count();

        $preinscritosNoPagados = $totalGeneral - $preInscritosPagados;

        // Contar por grado_id utilizando INNER JOINs para imitar el comportamiento previo pero 100x más rápido
        $gradoCount = DB::table('pre_inscripcions')
                        ->join('postulantes', 'pre_inscripcions.postulante_id', '=', 'postulantes.id')
                        ->join('inscripcions', 'postulantes.id', '=', 'inscripcions.postulante_id')
                        ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
                        ->select('programas.grado_id', DB::raw('count(*) as total'))
                        ->groupBy('programas.grado_id')
                        ->pluck('total', 'grado_id');

        return [
            'totalPre_inscritos' => $totalPre_Inscritos,
            'doctorado' => $gradoCount[1] ?? 0,
            'maestria' => $gradoCount[2] ?? 0,
            'segunda_especialidad' => $gradoCount[3] ?? 0,
            'preInscritosPagados' => $preInscritosPagados,
            'preInscritosNoPagados' => $preinscritosNoPagados
        ];
    }

    /**
     * Get table summary of pre-inscripciones by programa
     */
    public function getResumenTabla(): array
    {
        $programas = Programa::with(['grado', 'facultad'])->get();

        return $programas->map(function ($programa) {

            $preinscritos = $this->repository->countByPrograma($programa->id);
            $cobertura = $programa->vacantes > 0
                ? round(($preinscritos / $programa->vacantes) * 100, 2)
                : 0;

            return [
                'id' => $programa->id,
                'grado_programa' =>  ucfirst(strtolower($programa->grado->nombre)) . ' en ' . $programa->nombre,
                'facultad' => $programa->facultad->siglas,
                'preinscritos' => $preinscritos,
                'vacantes' => $programa->vacantes,
                'cobertura' => $cobertura,
            ];
        })->toArray();
    }

    /**
     * Get general summary for comision
     */
    public function getResumenGeneral(): array
    {
        $programas = Programa::with(['grado', 'facultad'])->withCount('preInscripciones')->get();
        $comision = ComisionAdmision::all();

        $resumen = [];

        foreach ($comision as $miembro) {
            $programasFiltrados = $miembro->resumen_completo
                ? $programas
                : $programas->where('facultad_id', $miembro->facultad_id);

            $detalleProgramas = [];
            $totales = [];
            $totalGeneral = 0;

            foreach ($programasFiltrados as $programa) {
                $cantidad = $programa->pre_inscripciones_count ?? 0;
                $totalGeneral += $cantidad;

                $abreviatura_grado = match ($programa->grado->id) {
                    1 => 'Doctorado',
                    2 => 'Maestria',
                    3 => 'Segunda Especialidad Profesional',
                    default => 'N/A'
                };

                $gradoNombre = strtoupper(trim($programa->grado->nombre));
                if (!isset($totales[$gradoNombre])) {
                    $totales[$gradoNombre] = 0;
                }
                $totales[$gradoNombre] += $cantidad;

                $cobertura = $programa->vacantes > 0
                    ? round(($cantidad / $programa->vacantes) * 100, 2)
                    : 0;

                $detalleProgramas[] = [
                    'programa' => $abreviatura_grado . ' - ' . $programa->nombre,
                    'facultad' => $programa->facultad->siglas,
                    'preinscritos' => $cantidad,
                    'vacantes' => $programa->vacantes,
                    'cobertura' => $cobertura . '%',
                ];
            }

            $totales['TOTAL'] = $totalGeneral;

            $resumen[] = [
                'comision' => [
                    'nombre' => $miembro->ap_paterno . ' ' . $miembro->ap_materno . ' ' . $miembro->nombres,
                    'email' => $miembro->email,
                    'resumen_completo' => (bool) $miembro->resumen_completo,
                    'facultad' => $miembro->facultad->siglas ?? null,
                ],
                'resumen_general' => $totales,
                'programas' => $detalleProgramas,
            ];
        }

        return $resumen;
    }
}
