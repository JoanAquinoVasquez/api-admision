<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery\Matcher\Not;

class ResultadosController extends Controller
{
    /**
     * Método para obtener todos los registros
     */
    public function index()
    {
        try {
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
                $pagosPension   = $pagos->filter(fn($v) => $v->conceptoPago->cod_concepto === '00000003')->count();

                return [
                    'inscripcion_id' => $inscripcion->id,
                    'programa_id'    => $inscripcion->programa->id,
                    'grado_id'       => $inscripcion->programa->grado->id,
                    'apellidos'      => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno,
                    'nombres'        => $inscripcion->postulante->nombres,
                    'tipo_doc'       => $inscripcion->postulante->tipo_doc,
                    'num_iden'       => $dni,
                    'celular'        => $inscripcion->postulante->celular,
                    'email'          => $inscripcion->postulante->email,
                    'nota_final'     => round(
                        floatval($inscripcion->nota->cv) +
                            floatval($inscripcion->nota->entrevista) +
                            floatval($inscripcion->nota->examen),
                        3
                    ),
                    'programa'       => $inscripcion->programa->nombre,
                    'grado'          => $inscripcion->programa->grado->nombre,
                    'matricula_pagada' => $pagosMatricula,
                    'pension_pagada'   => $pagosPension,
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
            $conMeritoGeneral = $conMeritoPrograma
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

            return response()->json($conMeritoGeneral, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los postulantes con mérito',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reportIngresantesPrograma()
    {
        try {
            // Obtener todos los programas activos con sus relaciones necesarias
            $programas = Programa::with(['facultad', 'grado', 'inscripciones.nota'])
                ->where('estado', true)
                ->get()
                ->map(function ($programa) {
                    // Filtrar solo los ingresantes válidos
                    $ingresantes = $programa->inscripciones->filter(function ($inscripcion) {
                        $nota = $inscripcion->nota;
                        return $nota &&
                            is_numeric($nota->cv) &&
                            is_numeric($nota->entrevista) &&
                            is_numeric($nota->examen);
                    });

                    return (object)[
                        'facultad'        => $programa->facultad ? $programa->facultad->siglas : 'N/A',
                        'grado'           => $programa->grado ? $programa->grado->nombre : 'N/A',
                        'programa'        => $programa->nombre,
                        'total_ingresantes' => $ingresantes->count(),
                    ];
                })
                ->sortByDesc('total_ingresantes')
                ->values(); // Reindexar

            $pdf = Pdf::loadView('reporte-ingresantes-top', [
                'programas' => $programas,
                'fechaHora' => now(),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("reporte-ingresantes-top.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function ingresantesPorPrograma()
    {
        try {
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

            $resultado = $ingresantesPorPrograma->map(function ($ingresantes, $programaId) use ($inscritosPorPrograma) {
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

                    'grado_programa'     => match ($programa->grado_id) {
                        1 => 'DOC - ' . $programa->nombre,
                        2 => 'MAE - ' . $programa->nombre,
                        3 => 'SEG - ' . $programa->nombre,
                        default => $programa->nombre,
                    },

                    'ingresantes_total'   => $ingresantes->count(),
                    'ingresantes_hombres' => $hombresIngresantes,
                    'ingresantes_mujeres' => $mujeresIngresantes,

                    'inscritos_total'     => $totalInscritos,
                    'inscritos_hombres'   => $hombresInscritos,
                    'inscritos_mujeres'   => $mujeresInscritos,

                    'promedio_nota'       => $promedioNota,
                ];
            })->sortByDesc('promedio_nota')->values();

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ], 500);
        }
    }


    public function resumenPorEdad()
    {
        try {
            $postulantes = Postulante::with(['inscripcion.nota'])->get();
            $resumen = [];

            foreach ($postulantes as $p) {
                if (!$p->fecha_nacimiento) continue;

                $edad = Carbon::parse($p->fecha_nacimiento)->age;
                if ($edad < 22) continue;

                $rango = $this->rangoEdadPosgrado($edad);

                // Inicializar el rango si no existe
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

                if (!$ins) continue;

                // Contar estados simples
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

                // Contar ingresantes, ausentes y desistentes
                $nota = $ins->nota;
                if ($nota) {
                    $tieneCV = !is_null($nota->cv);
                    $tieneEntrevista = !is_null($nota->entrevista);
                    $tieneExamen = !is_null($nota->examen);
                    $notaExamenCero = floatval($nota->examen) === 0.0;

                    $ingresante = $tieneCV && $tieneEntrevista && $tieneExamen;
                    $ausente = $ins->estado === 1 && $tieneCV && !$tieneEntrevista && (!$tieneExamen || $notaExamenCero);
                    $desiste = $ins->estado === 1 && !$tieneCV && !$tieneEntrevista && (!$tieneExamen || $notaExamenCero);

                    if ($ingresante) $resumen[$rango]['ingresantes']++;
                    if ($ausente) $resumen[$rango]['ausentes']++;
                    if ($desiste) $resumen[$rango]['desiste']++;
                }
            }

            return response()->json(array_values($resumen));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function rangoEdadPosgrado($edad)
    {
        if ($edad <= 25) return '22-25';
        elseif ($edad <= 30) return '26-30';
        elseif ($edad <= 35) return '31-35';
        elseif ($edad <= 40) return '36-40';
        elseif ($edad <= 45) return '41-45';
        elseif ($edad <= 50) return '46-50';
        else return '51+';
    }


    public function resumenGeneral()
    {
        try {
            $postulantes = Postulante::with('inscripcion.nota', 'inscripcion.programa.grado')->get();

            $resumenPorGrado = [];

            foreach ($postulantes as $p) {
                $inscripcion = $p->inscripcion;

                if (!$inscripcion || !$inscripcion->programa || !$inscripcion->programa->grado) {
                    continue;
                }

                $gradoNombre = $inscripcion->programa->grado->nombre ?? 'Otro';

                // Inicializar grado si no existe
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

            // Reindexar como array plano
            $resumenPorGrado = array_values($resumenPorGrado);

            return response()->json($resumenPorGrado);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function histogramaNotas(Request $request)
    {
        try {
            // Obtener postulantes con notas completas
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
                return response()->json([
                    'message' => 'No hay notas disponibles para construir el histograma.'
                ]);
            }

            $min = floor($notasFinales->min());
            $max = ceil($notasFinales->max());
            $n = count($notasFinales);

            // Calcular número de intervalos (Sturges) o usar el que se pase
            $numIntervalos = $request->input('intervalos') ?? max(1, ceil(log($n, 2) + 1));

            // Ancho fijo de intervalo
            $ancho = ceil(($max - $min + 1) / $numIntervalos); // +1 para incluir el max

            // Generar intervalos iguales
            $histograma = [];
            for ($i = 0; $i < $numIntervalos; $i++) {
                $inicio = $min + $i * $ancho;
                $fin = $inicio + $ancho - 1;
                $label = $inicio . '-' . $fin;
                $histograma[$label] = 0;
            }

            // Clasificar notas
            foreach ($notasFinales as $nota) {
                $index = floor(($nota - $min) / $ancho);
                if ($index >= $numIntervalos) {
                    $index = $numIntervalos - 1; // en caso la nota esté justo en el máximo
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

            return response()->json([
                'min_nota' => $min,
                'max_nota' => $max,
                'promedio_general' => round($notasFinales->avg(), 2),
                'intervalos' => $numIntervalos,
                'ancho_intervalo' => $ancho,
                'histograma' => $resultado,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
