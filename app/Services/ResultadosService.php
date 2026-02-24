<?php

namespace App\Services;

use App\Models\Inscripcion;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ResultadosService
{
    /**
     * Obtener el ranking de méritos general y por programa
     */
    public function getRankingMerito(): Collection
    {
        $inscripciones = Inscripcion::with([
            'programa.grado',
            'postulante',
            'nota',
        ])
            ->whereHas('programa', function ($q) {
                $q->where('estado', 1);
            })
            ->whereHas('nota', function ($q) {
                $q->whereNotNull('cv')
                    ->whereNotNull('entrevista')
                    ->whereNotNull('examen');
            })
            ->get();

        // Obtener todos los vouchers relevantes (solo matrícula y pensión)
        $vouchers = Voucher::with('conceptoPago')
            ->whereHas('conceptoPago', function ($q) {
                $q->whereIn('cod_concepto', ['00000001', '00000003']);
            })
            ->get()
            ->groupBy('num_iden');

        // Mapear con nota final, datos requeridos y pagos
        $postulantes = $inscripciones->map(function ($inscripcion) use ($vouchers) {
            $dni = $inscripcion->postulante->num_iden;
            $pagos = $vouchers->get($dni, collect());

            $pagosMatricula = $pagos->filter(fn($v) => $v->conceptoPago->cod_concepto === '00000001')->count();
            $pagosPension = $pagos->filter(fn($v) => $v->conceptoPago->cod_concepto === '00000003')->count();

            return [
                'inscripcion_id' => $inscripcion->id,
                'programa_id' => $inscripcion->programa->id,
                'grado_id' => $inscripcion->programa->grado->id,
                'apellidos' => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno,
                'nombres' => $inscripcion->postulante->nombres,
                'tipo_doc' => $inscripcion->postulante->tipo_doc,
                'num_iden' => $dni,
                'celular' => $inscripcion->postulante->celular,
                'email' => $inscripcion->postulante->email,
                'nota_final' => round(
                    floatval($inscripcion->nota->cv) +
                    floatval($inscripcion->nota->entrevista) +
                    floatval($inscripcion->nota->examen),
                    3
                ),
                'programa' => $inscripcion->programa->nombre,
                'grado' => $inscripcion->programa->grado->nombre,
                'matricula_pagada' => $pagosMatricula,
                'pension_pagada' => $pagosPension,
            ];
        });

        // Asignar mérito por programa
        $conMeritoPrograma = $postulantes
            ->groupBy('programa_id')
            ->flatMap(function ($grupo) {
                $ordenados = $grupo->sortByDesc('nota_final')->values();

                $ultimoMerito = 0;
                $ultimoNota = null;
                $contador = 0;

                return $ordenados->map(function ($item) use (&$ultimoMerito, &$ultimoNota, &$contador) {
                    $contador++;
                    if ($item['nota_final'] !== $ultimoNota) {
                        $ultimoMerito = $contador;
                        $ultimoNota = $item['nota_final'];
                    }
                    return array_merge($item, ['merito_programa' => $ultimoMerito]);
                });
            });

        // Asignar mérito general
        return $conMeritoPrograma
            ->sortByDesc('nota_final')
            ->values()
            ->map(function ($item) {
                static $ultimoNota = null;
                static $ultimoMerito = 0;
                static $contador = 0;

                $contador++;
                if ($item['nota_final'] !== $ultimoNota) {
                    $ultimoMerito = $contador;
                    $ultimoNota = $item['nota_final'];
                }

                return array_merge($item, ['merito_general' => $ultimoMerito]);
            });
    }

    /**
     * Obtener estadísticas de ingresantes por programa
     */
    public function getIngresantesPorPrograma(): Collection
    {
        $postulantesIngresantes = Postulante::whereHas('inscripcion.nota', function ($q) {
            $q->whereNotNull('cv')
                ->whereNotNull('entrevista')
                ->whereNotNull('examen');
        })
            ->whereHas('inscripcion.programa', function ($q) {
                $q->where('estado', 1);
            })
            ->with('inscripcion.programa.grado', 'inscripcion.nota')
            ->get();

        $postulantesInscritos = Postulante::whereHas('inscripcion.programa', function ($q) {
            $q->where('estado', 1);
        })
            ->with('inscripcion.programa.grado')
            ->get();

        $ingresantesPorPrograma = $postulantesIngresantes->groupBy(function ($p) {
            return $p->inscripcion->programa->id;
        });

        $inscritosPorPrograma = $postulantesInscritos->groupBy(function ($p) {
            return $p->inscripcion->programa->id;
        });

        return $ingresantesPorPrograma->map(function ($ingresantes, $programaId) use ($inscritosPorPrograma) {
            $programa = $ingresantes->first()->inscripcion->programa;

            $hombresIngresantes = $ingresantes->where('sexo', 'M')->count();
            $mujeresIngresantes = $ingresantes->where('sexo', 'F')->count();

            $inscritos = $inscritosPorPrograma[$programaId] ?? collect();
            $totalInscritos = $inscritos->count();
            $hombresInscritos = $inscritos->where('sexo', 'M')->count();
            $mujeresInscritos = $inscritos->where('sexo', 'F')->count();

            $notas = $ingresantes->map(function ($p) {
                $nota = $p->inscripcion->nota;
                return $nota->cv + $nota->entrevista + $nota->examen;
            });

            $promedioNota = round($notas->avg(), 2);

            return [
                'grado_programa' => match ($programa->grado_id) {
                    1 => 'DOC - ' . $programa->nombre,
                    2 => 'MAE - ' . $programa->nombre,
                    3 => 'SEG - ' . $programa->nombre,
                    default => $programa->nombre,
                },
                'ingresantes_total' => $ingresantes->count(),
                'ingresantes_hombres' => $hombresIngresantes,
                'ingresantes_mujeres' => $mujeresIngresantes,
                'inscritos_total' => $totalInscritos,
                'inscritos_hombres' => $hombresInscritos,
                'inscritos_mujeres' => $mujeresInscritos,
                'promedio_nota' => $promedioNota,
            ];
        })->sortByDesc('promedio_nota')->values();
    }

    /**
     * Obtener resumen de postulantes por rango de edad
     */
    public function getResumenPorEdad(): array
    {
        $postulantes = Postulante::with(['inscripcion.nota'])->get();
        $resumen = [];

        foreach ($postulantes as $p) {
            if (!$p->fecha_nacimiento)
                continue;

            $edad = Carbon::parse($p->fecha_nacimiento)->age;
            if ($edad < 22)
                continue;

            $rango = $this->rangoEdadPosgrado($edad);

            if (!isset($resumen[$rango])) {
                $resumen[$rango] = [
                    'rango_edad' => $rango,
                    'inscritos' => 0,
                    'pendientes' => 0,
                    'ingresantes' => 0,
                    'devolucion' => 0,
                    'reserva' => 0,
                    'ausentes' => 0,
                    'desiste' => 0,
                ];
            }

            $resumen[$rango]['inscritos']++;

            $ins = $p->inscripcion;
            if (!$ins)
                continue;

            switch ($ins->estado) {
                case 0:
                    $resumen[$rango]['pendientes']++;
                    break;
                case 2:
                    $resumen[$rango]['devolucion']++;
                    break;
                case 3:
                    $resumen[$rango]['reserva']++;
                    break;
            }

            $nota = $ins->nota;
            if ($nota) {
                $tieneCV = !is_null($nota->cv);
                $tieneEntrevista = !is_null($nota->entrevista);
                $tieneExamen = !is_null($nota->examen);
                $notaExamenCero = floatval($nota->examen) === 0.0;

                $ingresante = $tieneCV && $tieneEntrevista && $tieneExamen;
                $ausente = $ins->estado === 1 && $tieneCV && !$tieneEntrevista && (!$tieneExamen || $notaExamenCero);
                $desiste = $ins->estado === 1 && !$tieneCV && !$tieneEntrevista && (!$tieneExamen || $notaExamenCero);

                if ($ingresante)
                    $resumen[$rango]['ingresantes']++;
                if ($ausente)
                    $resumen[$rango]['ausentes']++;
                if ($desiste)
                    $resumen[$rango]['desiste']++;
            }
        }

        return array_values($resumen);
    }

    /**
     * Obtener resumen general por grado
     */
    public function getResumenGeneral(): array
    {
        $postulantes = Postulante::with('inscripcion.nota', 'inscripcion.programa.grado')->get();
        $resumenPorGrado = [];

        foreach ($postulantes as $p) {
            $inscripcion = $p->inscripcion;

            if (!$inscripcion || !$inscripcion->programa || !$inscripcion->programa->grado) {
                continue;
            }

            $gradoNombre = $inscripcion->programa->grado->nombre ?? 'Otro';

            if (!isset($resumenPorGrado[$gradoNombre])) {
                $resumenPorGrado[$gradoNombre] = [
                    'grado' => $gradoNombre,
                    'inscritos' => 0,
                    'pendientes' => 0,
                    'reserva' => 0,
                    'devolucion' => 0,
                    'desiste' => 0,
                    'ausentes' => 0,
                    'ingresantes' => 0,
                ];
            }

            $resumenPorGrado[$gradoNombre]['inscritos']++;

            $estado = $inscripcion->estado;
            $nota = optional($inscripcion)->nota;

            switch ($estado) {
                case 0:
                    $resumenPorGrado[$gradoNombre]['pendientes']++;
                    break;
                case 2:
                    $resumenPorGrado[$gradoNombre]['reserva']++;
                    break;
                case 3:
                    $resumenPorGrado[$gradoNombre]['devolucion']++;
                    break;
                case 1:
                    if ($nota) {
                        $cv = $nota->cv;
                        $entrevista = $nota->entrevista;
                        $examen = $nota->examen;

                        if (is_null($cv) || $cv == 0) {
                            $resumenPorGrado[$gradoNombre]['desiste']++;
                        } elseif (!is_null($cv) && (!isset($entrevista) || !isset($examen) || $examen == 0)) {
                            $resumenPorGrado[$gradoNombre]['ausentes']++;
                        } elseif (!is_null($cv) && !is_null($entrevista) && !is_null($examen)) {
                            $resumenPorGrado[$gradoNombre]['ingresantes']++;
                        }
                    } else {
                        $resumenPorGrado[$gradoNombre]['desiste']++;
                    }
                    break;
            }
        }

        return array_values($resumenPorGrado);
    }

    /**
     * Generar histograma de notas
     */
    public function getHistogramaNotas(?int $intervalos = null): array
    {
        $postulantes = Postulante::with('inscripcion.nota')->get();

        $notasFinales = $postulantes
            ->map(function ($p) {
                $nota = optional(optional($p->inscripcion)->nota);
                if ($nota && !is_null($nota->cv) && !is_null($nota->entrevista) && !is_null($nota->examen)) {
                    return $nota->examen + $nota->entrevista + $nota->cv;
                }
                return null;
            })
            ->filter()
            ->values();

        if ($notasFinales->isEmpty()) {
            return [];
        }

        $min = floor($notasFinales->min());
        $max = ceil($notasFinales->max());
        $n = count($notasFinales);

        $numIntervalos = $intervalos ?? max(1, ceil(log($n, 2) + 1));
        $ancho = ceil(($max - $min + 1) / $numIntervalos);

        $histograma = [];
        for ($i = 0; $i < $numIntervalos; $i++) {
            $inicio = $min + $i * $ancho;
            $fin = $inicio + $ancho - 1;
            $label = $inicio . '-' . $fin;
            $histograma[$label] = 0;
        }

        foreach ($notasFinales as $nota) {
            $index = floor(($nota - $min) / $ancho);
            if ($index >= $numIntervalos) {
                $index = $numIntervalos - 1;
            }
            $inicio = $min + $index * $ancho;
            $fin = $inicio + $ancho - 1;
            $label = $inicio . '-' . $fin;
            $histograma[$label]++;
        }

        $resultado = collect($histograma)->map(function ($cantidad, $rango) {
            return [
                'rango' => $rango,
                'cantidad' => $cantidad,
            ];
        })->values();

        return [
            'min_nota' => $min,
            'max_nota' => $max,
            'promedio_general' => round($notasFinales->avg(), 2),
            'intervalos' => $numIntervalos,
            'ancho_intervalo' => $ancho,
            'histograma' => $resultado,
        ];
    }

    private function rangoEdadPosgrado($edad): string
    {
        if ($edad <= 25)
            return '22-25';
        elseif ($edad <= 30)
            return '26-30';
        elseif ($edad <= 35)
            return '31-35';
        elseif ($edad <= 40)
            return '36-40';
        elseif ($edad <= 45)
            return '41-45';
        elseif ($edad <= 50)
            return '46-50';
        else
            return '51+';
    }
}
