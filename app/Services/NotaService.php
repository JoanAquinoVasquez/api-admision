<?php

namespace App\Services;

use App\Imports\NotasExamenImport;
use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\Programa;
use App\Repositories\Contracts\NotaRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class NotaService
{
    public function __construct(
        protected NotaRepositoryInterface $notaRepository
    ) {
    }

    /**
     * Import exam grades from Excel file
     */
    public function importExamGrades(UploadedFile $file): array
    {
        try {
            Excel::import(new NotasExamenImport, $file);

            return [
                'success' => true,
                'message' => 'Las Notas del Examen de Admisión se han importado correctamente.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al importar las notas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Save or update interview grade
     */
    public function saveInterviewGrade(int $inscripcionId, float $notaEntrevista): array
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion) {
            return [
                'success' => false,
                'message' => 'Inscripción no encontrada',
            ];
        }

        $nota = Nota::where('inscripcion_id', $inscripcionId)->first();

        if (!$nota) {
            Nota::create([
                'inscripcion_id' => $inscripcionId,
                'entrevista' => $notaEntrevista,
            ]);
        } else {
            $nota->update(['entrevista' => $notaEntrevista]);
        }

        return [
            'success' => true,
            'message' => 'Nota guardada correctamente',
        ];
    }

    /**
     * Calculate final grades with merit ranking
     */
    public function calculateFinalGrades(): Collection
    {
        $programas = Programa::with([
            'grado',
            'inscripciones' => function ($query) {
                $query->where([
                    'val_digital' => 1,
                    'val_fisico' => true,
                    'estado' => true,
                ])
                    ->whereHas('nota', function ($notaQuery) {
                        $notaQuery->whereNotNull('cv')
                            ->whereNotNull('entrevista')
                            ->whereNotNull('examen')
                            ->whereRaw('cv REGEXP "^[0-9]+(\.[0-9]+)?$"')
                            ->whereRaw('entrevista REGEXP "^[0-9]+(\.[0-9]+)?$"')
                            ->whereRaw('examen REGEXP "^[0-9]+(\.[0-9]+)?$"');
                    })
                    ->with(['postulante', 'nota']);
            },
        ])->where('estado', true)->get();

        // Ordenar y asignar mérito
        $programas->each(function ($programa) {
            // 1. Calcular puntaje final
            foreach ($programa->inscripciones as $inscripcion) {
                $nota = $inscripcion->nota;
                $inscripcion->puntaje_final = floatval($nota->cv) + floatval($nota->entrevista) + floatval($nota->examen);
            }

            // 2. Ordenar por puntaje final descendente
            $inscripcionesOrdenadas = $programa->inscripciones->sortByDesc('puntaje_final')->values();

            // 3. Asignar mérito considerando empates
            $merito = 1;
            $ultimoPuntaje = null;
            $contadorEmpates = 0;

            foreach ($inscripcionesOrdenadas as $index => $inscripcion) {
                if ($inscripcion->puntaje_final === $ultimoPuntaje) {
                    $inscripcion->merito = $merito;
                    $contadorEmpates++;
                } else {
                    $merito = $index + 1;
                    $inscripcion->merito = $merito;
                    $ultimoPuntaje = $inscripcion->puntaje_final;
                    $contadorEmpates = 0;
                }
            }

            $programa->inscripciones = $inscripcionesOrdenadas;
        });
        return $programas;
    }

    /**
     * Get evaluation summary by program
     */
    public function getEvaluationSummary(): Collection
    {
        $programas = Programa::with(['facultad', 'grado', 'docente', 'inscripciones.nota'])->get();

        return $programas->map(function ($programa) {
            $inscripciones = $programa->inscripciones;

            $total_inscritos = $inscripciones->count();
            $aptos = $inscripciones->where('val_fisico', 1)->count();

            // Evaluated inscriptions: those with valid CV grade
            $evaluados = $inscripciones->filter(function ($inscripcion) {
                $nota = $inscripcion->nota;
                return $nota && is_numeric($nota->cv);
            })->count();

            $cobertura = $aptos > 0 ? round(($evaluados / $aptos) * 100, 2) : 0;

            $abreviatura_grado = match ($programa->grado->id) {
                1 => 'DOC',
                2 => 'MAE',
                3 => 'SEG',
                default => 'N/A'
            };

            return [
                'id' => $programa->id,
                'grado_id' => $programa->grado->id,
                'grado_programa' => $abreviatura_grado . ' - ' . $programa->nombre,
                'facultad' => $programa->facultad->siglas,
                'docente_id' => $programa->docente?->id ?? null,
                'docente_apellidos' => ($programa->docente?->ap_paterno . ' ' . $programa->docente?->ap_materno) ?? 'No asignado',
                'docente' => $programa->docente?->nombres ?? 'No asignado',
                'inscritos' => $total_inscritos,
                'aptos' => $aptos,
                'evaluados' => $evaluados,
                'cobertura' => $cobertura,
            ];
        });
    }

    /**
     * Get daily grades summary
     */
    public function getDailyGradesSummary(): Collection
    {
        $notas = Nota::whereNotNull('cv')
            ->with('inscripcion.programa.docente', 'inscripcion.programa.grado')
            ->get();

        return $notas->map(function ($nota) {
            $docente = $nota->inscripcion?->programa?->docente;

            return [
                'created_at' => $nota->created_at->toDateString(),
                'grado_id' => $nota->inscripcion->programa->grado->id,
                'grado_nombre' => $nota->inscripcion->programa->grado->nombre,
                'docente' => $docente->id ?? 'No asignado',
                'docente_nombres' => $docente->nombres ?? 'No asignado',
                'docente_apellidos' => ($docente->ap_paterno ?? '') . ' ' . ($docente->ap_materno ?? ''),
            ];
        });
    }

    public function getNotasDiariasSummary(): Collection
    {
        $notas = Nota::whereNotNull('cv')
            ->with('inscripcion.programa.docente', 'inscripcion.programa.grado')
            ->get();

        return $notas->map(function ($nota) {
            $docente = $nota->inscripcion?->programa?->docente;

            return [
                'created_at' => $nota->created_at->toDateString(),
                'grado_id' => $nota->inscripcion->programa->grado->id,
                'grado_nombre' => $nota->inscripcion->programa->grado->nombre,
                'docente' => $docente->id ?? 'No asignado',
                'docente_nombres' => $docente->nombres ?? 'No asignado',
                'docente_apellidos' => ($docente->ap_paterno ?? '') . ' ' . ($docente->ap_materno ?? ''),
            ];
        });
    }
}
