<?php

namespace App\Services;

use App\Models\ComisionAdmision;
use App\Models\PreInscripcion;
use App\Models\Programa;
use App\Models\Voucher;
use App\Repositories\Contracts\PreInscripcionRepositoryInterface;

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
        $preInscripciones = PreInscripcion::with([
            'programa.grado',
            'postulante.inscripcion.programa.grado',
            'postulante.inscripcion.voucher'
        ])->get();

        $numIdens = $preInscripciones->pluck('num_iden');
        $vouchersNumIden = Voucher::whereIn('num_iden', $numIdens)->pluck('num_iden')->toArray();

        $totalPre_Inscritos = $preInscripciones->whereNotNull('postulante_id')->count();
        $preInscritosPagados = 0;
        $preinscritosNoPagados = 0;
        $grado1 = 0;
        $grado2 = 0;
        $grado3 = 0;

        $preInscritosConPostulante = $preInscripciones->filter(fn($p) => $p->postulante_id !== null);

        $preInscritosConPostulante->each(function ($preInscripcion) use (&$grado1, &$grado2, &$grado3) {
            $inscripcion = $preInscripcion->postulante->inscripcion;
            if ($inscripcion && $inscripcion->programa && $inscripcion->programa->grado) {
                match ($inscripcion->programa->grado->id) {
                    1 => $grado1++,
                    2 => $grado2++,
                    3 => $grado3++,
                    default => null
                };
            }
        });

        $preInscripciones->each(function ($preInscripcion) use ($vouchersNumIden, &$preInscritosPagados, &$preinscritosNoPagados) {
            if (in_array($preInscripcion->num_iden, $vouchersNumIden)) {
                $preInscritosPagados++;
            } else {
                $preinscritosNoPagados++;
            }
        });

        return [
            'totalPre_inscritos' => $totalPre_Inscritos,
            'doctorado' => $grado1,
            'maestria' => $grado2,
            'segunda_especialidad' => $grado3,
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
            $abreviatura_grado = match ($programa->grado->id) {
                1 => 'DOC',
                2 => 'MAE',
                3 => 'SEG',
                default => 'N/A'
            };

            $preinscritos = $this->repository->countByPrograma($programa->id);
            $cobertura = $programa->vacantes > 0
                ? round(($preinscritos / $programa->vacantes) * 100, 2)
                : 0;

            return [
                'id' => $programa->id,
                'grado_programa' => $abreviatura_grado . ' - ' . $programa->nombre,
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
        $programas = Programa::with(['grado', 'preInscripciones', 'facultad'])->get();
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
                $cantidad = $programa->preInscripciones->count();
                $totalGeneral += $cantidad;

                $abreviatura_grado = match ($programa->grado->id) {
                    1 => 'DOC',
                    2 => 'MAE',
                    3 => 'SEG',
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
