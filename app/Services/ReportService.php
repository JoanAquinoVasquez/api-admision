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
            28 => 'AULA 01',
            38 => 'AULA 02',
            8 => 'AULA 17',
            37 => 'AULA 03',
            33 => 'AULA 05',
            22 => 'AULA 08',
            35 => 'AULA 09',
            41 => 'AULA 10',
            4 => 'AULA 11',
            13 => 'AULA 12',
            29 => 'AULA 13',
            43 => 'AULA 14',
            11 => 'AULA 15',
            31 => 'AULA 16',
            34 => 'AULA 17',
            24 => 'AULA 18',
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
            28 => 'AULA 01',
            38 => 'AULA 02',
            8 => 'AULA 17',
            37 => 'AULA 03',
            33 => 'AULA 05',
            22 => 'AULA 08',
            35 => 'AULA 09',
            41 => 'AULA 10',
            4 => 'AULA 11',
            13 => 'AULA 12',
            29 => 'AULA 13',
            43 => 'AULA 14',
            11 => 'AULA 15',
            31 => 'AULA 16',
            34 => 'AULA 17',
            24 => 'AULA 18',
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

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        $pdf = Pdf::loadView('postulante-aptos-final-firmas', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte_aptos_firmas_" . now()->format('d-m-Y_His') . ".pdf");
    }

    public function getResumenGeneralInscripcion()
    {
        $programas = Programa::with(['grado', 'inscripciones', 'facultad'])->get();
        $comision = ComisionAdmision::all();
        $vouchers = Voucher::all();

        $resumen = [];

        foreach ($comision as $miembro) {
            $programasFiltrados = $miembro->resumen_completo
                ? $programas
                : $programas->where('facultad_id', $miembro->facultad_id);

            $detalleProgramas = [];
            $totales = [];
            $totalGeneral = 0;

            foreach ($programasFiltrados as $programa) {
                $cantidad = $programa->inscripciones->count();
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
                    'inscritos' => $cantidad,
                    'vacantes' => $programa->vacantes,
                    'cobertura' => $cobertura . '%',
                ];
            }

            $totales['TOTAL'] = $totalGeneral;

            $vouchersArray = [];
            $vouchersArray['VOUCHERS_BN'] = $vouchers->where('agencia', '!=', '0987')->count();
            $vouchersArray['VOUCHERS_PY'] = $vouchers->where('agencia', '0987')->count();
            $vouchersArray['VOUCHERS_TOTAL'] = $vouchers->count();

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
        $programas = Programa::with(['grado', 'facultad', 'conceptoPago', 'inscripciones'])->get();

        return $programas->map(function ($programa) {
            // Asignar abreviatura del grado
            $abreviatura_grado = match ($programa->grado->id) {
                1 => 'DOC',
                2 => 'MAE',
                3 => 'SEG',
                default => 'N/A'
            };

            // Calcular cobertura
            $cobertura = $programa->vacantes > 0
                ? round(($programa->inscripciones->count() / $programa->vacantes) * 100, 2)
                : 0;

            // Calcular recaudación de 0970 y 0971
            if ($programa->concepto_pago_id === 3) {
                $recaudacion = 'S/. ' . number_format($programa->inscripciones->count() * 200, 2, '.', ',');
            } else {
                $recaudacion = 'S/. ' . number_format($programa->inscripciones->count() * ($programa->conceptoPago->monto ?? 0), 2, '.', ',');
            }

            // Contar validados
            $val_digital = $programa->inscripciones->where('val_digital', 1)->count();
            $val_fisico = $programa->inscripciones->where('val_fisico', 1)->count();

            return [
                'id' => $programa->id,
                'grado_programa' => $abreviatura_grado . ' - ' . $programa->nombre,
                'facultad' => $programa->facultad->siglas,
                'inscritos' => $programa->inscripciones->count(),
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
        $inscripciones = Inscripcion::with([
            'programa.grado',
            'programa.facultad',
            'postulante.documentos',
            'postulante.distrito.provincia.departamento',
            'voucher.conceptoPago'
        ])->get();

        // Totales generales
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
                    'porcentaje' => $totalInscritos > 0 ? round(($valDigital1 / ($valDigital0 + $valDigital1 + $valDigital2)) * 100, 1) : 0.0
                ],
                'fisico' => [
                    'faltantes' => $valFisico0,
                    'recepcionados' => $valFisico1,
                    'porcentaje' => $totalInscritos > 0 ? round(($valFisico1 / ($valFisico0 + $valFisico1)) * 100, 1) : 0.0
                ],
            ],
            'grados' => [
                'doc' => $grado1,
                'mae' => $grado2,
                'seg' => $grado3
            ]
        ];
    }

    /**
     * Get data for inscription charts
     */
    public function getResumenInscripcionGraficoData()
    {
        return Inscripcion::with('programa.grado')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'created_at' => $inscripcion->created_at,
                    'programa' => [
                        'grado' => [
                            'nombre' => $inscripcion->programa->grado->nombre ?? 'N/A',
                        ],
                    ],
                ];
            });
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
                        'facultad' => $programa->facultad ? $programa->facultad->siglas : 'N/A',
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
