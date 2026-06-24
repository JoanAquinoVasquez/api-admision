<?php

namespace App\Services;

use App\Exports\InscripcionDiarioExport;
use App\Exports\InscripcionDiarioFacultadExport;
use App\Exports\InscripcionesFinalesExport;
use App\Exports\InscripcionExport;
use App\Exports\InscripcionNotasFinalExport;
use App\Exports\PreinscripcionSinPagarExport;
use App\Models\ComisionAdmision;
use App\Models\Facultad;
use App\Models\Inscripcion;
use App\Models\Programa;
use App\Models\Voucher;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function __construct(
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected ProgramaRepositoryInterface $programaRepository
    ) {
    }

    /**
     * Generar reporte general de inscripciones
     */
    public function generateInscripcionReport(int $gradoId, int $programaId)
    {
        $nombreArchivo = 'reporte_inscripcion_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionExport($gradoId, $programaId), $nombreArchivo);
    }

    /**
     * Generar reporte diario de inscripciones
     */
    public function generateDailyReport()
    {
        $nombreArchivo = 'reporte_inscripcion_diario_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionDiarioExport, $nombreArchivo);
    }

    /**
     * Generar reporte diario de inscritos por facultad
     */
    public function generateFacultadReport()
    {
        $nombreArchivo = 'reporte_inscripcion_diario_facultad_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionDiarioFacultadExport, $nombreArchivo);
    }

    /**
     * Generar reporte de preinscritos sin pagar
     */
    public function generatePreinscritosSinPagarReport()
    {
        $nombreArchivo = 'reporte_pre-inscripcion_sin_pagar_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new PreinscripcionSinPagarExport, $nombreArchivo);
    }

    /**
     * Generar reporte final en Excel
     */
    public function generateFinalReportExcel()
    {
        $nombreArchivo = 'reporte_final_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionesFinalesExport, $nombreArchivo);
    }

    /**
     * Generar reporte de notas finales en Excel
     */
    public function generateNotasFinalReportExcel()
    {
        $nombreArchivo = 'reporte_resultados_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionNotasFinalExport, $nombreArchivo);
    }

    /**
     * Generar PDF de programas top
     */
    public function generateProgramasTopPDF()
    {
        $programas = $this->programaRepository->getTopProgramas();

        $pdf = Pdf::loadView('reporte-inscripcion-top', [
            'programas' => $programas,
            'fechaHora' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte-inscripcion-top_" . now()->format('d-m-Y_His') . ".pdf");
    }

    /**
     * Generar PDF de postulantes aptos (entrevista) por programa
     */
    public function generatePostulantesAptosPDF($idPrograma)
    {
        $inscripciones = \App\Models\Inscripcion::with(['postulante', 'programa.grado'])
            ->where('programa_id', $idPrograma)
            ->where('val_digital', 1)
            ->get();

        $pdf = Pdf::loadView('notas.postulantes-aptos', [
            'inscripciones' => $inscripciones,
            'fechaHora' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("postulantes_aptos_" . $idPrograma . "_" . now()->format('d-m-Y_His') . ".pdf");
    }

    /**
     * Generar PDF de postulantes aptos (entrevista) por múltiples programas
     */
    public function generatePostulantesAptosMultiplePDF()
    {
        $programas = \App\Models\Programa::with([
            'grado',
            'inscripciones' => function ($q) {
                $q->where('val_digital', 1)->with('postulante');
            }
        ])->where('estado', true)
            ->whereHas('inscripciones', function ($q) {
                $q->where('val_digital', 1);
            })
            ->get();

        $pdf = Pdf::loadView('notas.postulantes-aptos-multiple', [
            'programas' => $programas,
            'fechaHora' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("postulantes_aptos_multiple_" . now()->format('d-m-Y_His') . ".pdf");
    }

    /**
     * Generar PDF de programas no aperturados
     */
    public function generateProgramasNoAperturadosPDF()
    {
        try {
            $programas = Programa::with(['facultad', 'grado'])
                ->where('estado', 0)
                ->get()
                ->map(function ($programa) {
                    return (object) [
                        'facultad' => $programa->facultad ? $programa->facultad->siglas : 'N/A',
                        'grado' => $programa->grado ? $programa->grado->nombre : 'N/A',
                        'programa' => $programa->nombre,
                    ];
                })
                ->values();

            $pdf = Pdf::loadView('reporte-programas-no-aperturados', [
                'programas' => $programas,
                'fechaHora' => now(),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("reporte-programas-no-aperturados_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateProgramasAperturadosPDF()
    {
        try {
            $programas = Programa::with(['facultad', 'grado'])
                ->where('estado', 1)
                ->get()
                ->map(function ($programa) {
                    return (object) [
                        'facultad' => $programa->facultad ? $programa->facultad->siglas : 'N/A',
                        'grado' => $programa->grado ? $programa->grado->nombre : 'N/A',
                        'programa' => $programa->nombre,
                    ];
                })
                ->values();

            $pdf = Pdf::loadView('reporte-programas', [
                'programas' => $programas,
                'fechaHora' => now(),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("reporte-programas_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateFacultadPDF()
    {
        try {
            $facultades = Facultad::with(['programas.grado', 'programas.inscripciones'])
                ->get()
                ->map(function ($facultad) {
                    $programas = $facultad->programas->map(function ($programa) use ($facultad) {
                        $totalInscritos = $programa->inscripciones->count();

                        return (object) [
                            'grado' => $programa->grado ? $programa->grado->nombre : 'N/A',
                            'programa' => $programa->nombre,
                            'total_inscritos' => $totalInscritos,
                        ];
                    });

                    return (object) [
                        'facultad' => $facultad->nombre,
                        'programas' => $programas,
                    ];
                });

            $pdf = Pdf::loadView('reporte-inscripcion', [
                'facultades' => $facultades,
                'fechaHora' => now(),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("reporte-inscripcion_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la constancia en PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateFinalPdf()
    {
        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();
        $programasData = [];

        foreach ($idProgramas as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $programasData[] = [
                    'programa' => $inscripciones->first()->programa->nombre ?? 'Desconocido',
                    'grado' => $inscripciones->first()->programa->grado->nombre ?? 'Desconocido',
                    'inscripciones' => $inscripciones,
                    'docente' => $inscripciones->first()->programa->docente,
                ];
            }
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        $pdf = Pdf::loadView('postulante-aptos-final', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte_notasCV-multiple_" . now()->format('d-m-Y_His') . ".pdf");
    }

    public function generateFinalAulasPdf()
    {
        $aulasAsignadas = [
            10 => 'AULA 01',
            24 => 'AULA 02',
            31 => 'AULA 03',
            21 => 'AULA 05',
            29 => 'AULA 08',
            25 => 'AULA 09',
            27 => 'AULA 10',
            8  => 'AULA 11',
            7  => 'AULA 12',
            22 => 'AULA 13',
        ];

        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();
        $programasData = [];

        foreach ($idProgramas as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $programaNombre = $inscripciones->first()->programa->nombre ?? 'Desconocido';
                $gradoNombre = $inscripciones->first()->programa->grado->nombre ?? 'Desconocido';
                $docente = $inscripciones->first()->programa->docente;

                if ($idPrograma === 9) {
                    $inscripcionesGrupo1 = $inscripciones->take(30);
                    $inscripcionesGrupo2 = $inscripciones->slice(30)->values();

                    if ($inscripcionesGrupo1->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo1,
                            'docente' => $docente,
                            'aula' => 'AULA 06',
                        ];
                    }

                    if ($inscripcionesGrupo2->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo2,
                            'docente' => $docente,
                            'aula' => 'AULA 07',
                        ];
                    }
                } else {
                    $aula = $aulasAsignadas[$idPrograma] ?? 'Sin aula asignada';
                    $programasData[] = [
                        'programa' => $programaNombre,
                        'grado' => $gradoNombre,
                        'inscripciones' => $inscripciones,
                        'docente' => $docente,
                        'aula' => $aula,
                    ];
                }
            }
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        $pdf = Pdf::loadView('postulante-aptos-final-aulas', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte_aulas_" . now()->format('d-m-Y_His') . ".pdf");
    }

    public function generateFinalFirmasPdf()
    {
        $aulasAsignadas = [
            10 => 'AULA 01',
            24 => 'AULA 02',
            31 => 'AULA 03',
            21 => 'AULA 05',
            29 => 'AULA 08',
            25 => 'AULA 09',
            27 => 'AULA 10',
            8  => 'AULA 11',
            7  => 'AULA 12',
            22 => 'AULA 13',
        ];

        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();
        $programasData = [];

        foreach ($idProgramas as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $programaNombre = $inscripciones->first()->programa->nombre ?? 'Desconocido';
                $gradoNombre = $inscripciones->first()->programa->grado->nombre ?? 'Desconocido';
                $docente = $inscripciones->first()->programa->docente;

                if ($idPrograma === 9) {
                    $inscripcionesGrupo1 = $inscripciones->take(30);
                    $inscripcionesGrupo2 = $inscripciones->slice(30)->values();

                    if ($inscripcionesGrupo1->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo1,
                            'docente' => $docente,
                            'aula' => 'AULA 06',
                        ];
                    }

                    if ($inscripcionesGrupo2->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo2,
                            'docente' => $docente,
                            'aula' => 'AULA 07',
                        ];
                    }
                } else {
                    $aula = $aulasAsignadas[$idPrograma] ?? 'Sin aula asignada';
                    $programasData[] = [
                        'programa' => $programaNombre,
                        'grado' => $gradoNombre,
                        'inscripciones' => $inscripciones,
                        'docente' => $docente,
                        'aula' => $aula,
                    ];
                }
            }
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        $pdf = Pdf::loadView('postulante-aptos-final-firmas', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte_aptos_firmas_" . now()->format('d-m-Y_His') . ".pdf");
    }

    public function getResumenGeneralInscripcion()
    {
        $programas = Programa::with(['grado', 'facultad'])->withCount('inscripciones')->get();
        $comision = ComisionAdmision::all();

        // Extraer calculos de Vouchers a SQL
        $totalVouchers = Voucher::count();
        $vouchersPY = Voucher::where('agencia', '0987')->count();
        $vouchersBN = $totalVouchers - $vouchersPY;

        $resumen = [];

        foreach ($comision as $miembro) {
            $programasFiltrados = $miembro->resumen_completo
                ? $programas
                : $programas->where('facultad_id', $miembro->facultad_id);

            $detalleProgramas = [];
            $totales = [];
            $totalGeneral = 0;

            foreach ($programasFiltrados as $programa) {
                $cantidad = $programa->inscripciones_count ?? 0;
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
                    'inscritos' => $cantidad,
                    'vacantes' => $programa->vacantes,
                    'cobertura' => $cobertura . '%',
                ];
            }

            $totales['TOTAL'] = $totalGeneral;

            $vouchersArray = [];
            $vouchersArray['VOUCHERS_BN'] = $vouchersBN;
            $vouchersArray['VOUCHERS_PY'] = $vouchersPY;
            $vouchersArray['VOUCHERS_TOTAL'] = $totalVouchers;

            $resumen[] = [
                'comision' => [
                    'nombre' => $miembro->ap_paterno . ' ' . $miembro->ap_materno . ' ' . $miembro->nombres,
                    'email' => $miembro->email,
                    'resumen_completo' => (bool) $miembro->resumen_completo,
                    'facultad' => $miembro->facultad->siglas ?? null,
                ],
                'resumen_general' => $totales,
                'vouchers' => $vouchersArray,
                'programas' => $detalleProgramas,
            ];
        }

        return $resumen;
    }

    /**
     * Get inscription statistics
     */
    public function getInscripcionStats(): array
    {
        $inscripciones = $this->inscripcionRepository->all();

        $total = $inscripciones->count();
        $validadosDigital = $inscripciones->where('val_digital', 1)->count();
        $validadosFisico = $inscripciones->where('val_fisico', 1)->count();
        $pendientes = $total - $validadosDigital;

        return [
            'total' => $total,
            'validados_digital' => $validadosDigital,
            'validados_fisico' => $validadosFisico,
            'pendientes' => $pendientes,
        ];
    }

    /**
     * Get inscriptions grouped by program
     */
    public function getInscripcionesPorPrograma()
    {
        return $this->programaRepository->getProgramasWithInscripciones();
    }

    /**
     * Get summary of inscriptions for the frontend view
     */
    public function getResumenInscripcionData()
    {
        $programas = Programa::with(['grado', 'facultad', 'conceptoPago'])
            ->withCount([
                'inscripciones',
                'inscripciones as val_digital_count' => function ($query) {
                    $query->where('val_digital', 1);
                },
                'inscripciones as val_fisico_count' => function ($query) {
                    $query->where('val_fisico', 1);
                }
            ])->get();

        return $programas->map(function ($programa) {
            // Asignar abreviatura del grado
            $abreviatura_grado = match ($programa->grado->id) {
                1 => 'Doctorado',
                2 => 'Maestria',
                3 => 'Segunda Especialidad Profesional',
                default => 'N/A'
            };

            // Calcular cobertura
            $inscripcionesCount = $programa->inscripciones_count ?? 0;
            $cobertura = $programa->vacantes > 0
                ? round(($inscripcionesCount / $programa->vacantes) * 100, 2)
                : 0;

            // Calcular recaudación de 0970 y 0971
            if ($programa->concepto_pago_id === 3) {
                $recaudacion = 'S/. ' . number_format($inscripcionesCount * 200, 2, '.', ',');
            } else {
                $recaudacion = 'S/. ' . number_format($inscripcionesCount * ($programa->conceptoPago->monto ?? 0), 2, '.', ',');
            }

            // Contar validados usando withCount variables
            $val_digital = $programa->val_digital_count ?? 0;
            $val_fisico = $programa->val_fisico_count ?? 0;

            return [
                'id' => $programa->id,
                'grado_programa' => $abreviatura_grado . ' en ' . $programa->nombre,
                'facultad' => $programa->facultad->siglas,
                'inscritos' => $inscripcionesCount,
                'vacantes' => $programa->vacantes,
                'cobertura' => $cobertura,
                'recaudacion' => $recaudacion,
                'validados' => $val_digital,
                'aptos' => $val_fisico,
            ];
        });
    }

    /**
     * Get detailed status of inscriptions
     */
    public function getEstadoInscripcionData()
    {
        // Totales generales optimizados a DB raw counts
        $totalInscritos = Inscripcion::count();

        // Contadores de validaciones digitales
        $valDigital0 = Inscripcion::where('val_digital', 0)->count();
        $valDigital1 = Inscripcion::where('val_digital', 1)->count();
        $valDigital2 = Inscripcion::where('val_digital', 2)->count();

        // Contadores de validaciones físicas
        $valFisico0 = Inscripcion::where('val_fisico', 0)->count();
        $valFisico1 = Inscripcion::where('val_fisico', 1)->count();

        // Contadores por grado mediante agrupación en BD SQL
        $gradoCounts = \Illuminate\Support\Facades\DB::table('inscripcions')
                        ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
                        ->select('programas.grado_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                        ->groupBy('programas.grado_id')
                        ->pluck('total', 'grado_id');
                        
        $grado1 = $gradoCounts[1] ?? 0;
        $grado2 = $gradoCounts[2] ?? 0;
        $grado3 = $gradoCounts[3] ?? 0;

        return [
            'total_inscritos' => $totalInscritos,
            'validaciones' => [
                'digital' => [
                    'pendientes' => $valDigital0 + $valDigital2,
                    'validados' => $valDigital1,
                    'porcentaje' => $totalInscritos > 0 ? round(($valDigital1 / ($valDigital0 + $valDigital1 + $valDigital2)) * 100, 1) : 0.0
                ],
                'fisico' => [
                    'faltantes' => $valFisico0,
                    'recepcionados' => $valFisico1,
                    'porcentaje' => $totalInscritos > 0 ? round(($valFisico1 / ($valFisico0 + $valFisico1)) * 100, 1) : 0.0
                ],
            ],
            'grados' => [
                'doctorado' => $grado1,
                'maestria' => $grado2,
                'segunda_especialidad_profesional' => $grado3
            ]
        ];
    }

    /**
     * Get data for inscription charts
     */
    public function getResumenInscripcionGraficoData()
    {
        $inscripciones = Inscripcion::with(['programa.grado'])
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'created_at' => $inscripcion->created_at,
                    'type' => 'inscripcion',
                    'programa' => [
                        'grado' => [
                            'nombre' => $inscripcion->programa->grado->nombre ?? 'N/A',
                        ],
                    ],
                ];
            });

        $vouchers = Voucher::get()
            ->map(function ($voucher) {
                return [
                    // Usar fecha_pago si existe, de lo contrario created_at como fallback
                    'created_at' => $voucher->fecha_pago ?? $voucher->created_at,
                    'type' => 'pago',
                ];
            });

        return $inscripciones->concat($vouchers);
    }

    public function generateIngresantesTopPDF()
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

                    return (object) [
                        'facultad' => $programa->facultad ? $programa->facultad->nombre : 'N/A',
                        'grado' => $programa->grado ? $programa->grado->nombre : 'N/A',
                        'programa' => $programa->nombre,
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

            return $pdf->stream("reporte-ingresantes-top_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
