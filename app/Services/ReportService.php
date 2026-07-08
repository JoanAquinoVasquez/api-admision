<?php

namespace App\Services;

use App\Exports\InscripcionDiarioExport;
use App\Exports\InscripcionDiarioFacultadExport;
use App\Exports\InscripcionesFinalesExport;
use App\Exports\InscripcionExport;
use App\Exports\InscripcionNotasFinalExport;
use App\Exports\InscripcionesPersonalizadoExport;
use App\Exports\PreinscripcionSinPagarExport;
use App\Exports\IngresantesProgramaExport;
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
    public function generateNotasFinalReportExcel($gradoId = null, $programaId = null)
    {
        $nombreArchivo = 'reporte_resultados_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionNotasFinalExport($gradoId, $programaId), $nombreArchivo);
    }

    /**
     * Generar reporte personalizado en Excel
     */
    public function generatePersonalizadoReportExcel($gradoId = null, $programaId = null, $aperturado = null, $notasFilter = null, $search = null)
    {
        $nombreArchivo = 'reporte_personalizado_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new InscripcionesPersonalizadoExport($gradoId, $programaId, $aperturado, $notasFilter, $search), $nombreArchivo);
    }

    /**
     * Generar PDF de programas top
     */
    public function generateProgramasTopPDF()
    {
        $programas = $this->programaRepository->getTopProgramas();
        $totalGeneral = Inscripcion::count();

        $pdf = Pdf::loadView('reporte-inscripcion-top', [
            'programas' => $programas,
            'totalGeneral' => $totalGeneral,
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
        $inscripciones = \App\Models\Inscripcion::with(['postulante', 'programa.grado', 'programa.docenteEntrevista'])
            ->where('programa_id', $idPrograma)
            ->where('val_digital', 1)
            ->get()
            ->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->ap_materno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->nombres ?? '');
            })->values();

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
    public function generatePostulantesAptosMultiplePDF($gradoId = null, $programaIds = null)
    {
        $query = \App\Models\Programa::with([
            'grado',
            'docenteEntrevista',
            'inscripciones' => function ($q) {
                $q->where('val_digital', 1)->with('postulante');
            }
        ])->where('estado', true)
            ->whereHas('inscripciones', function ($q) {
                $q->where('val_digital', 1);
            });

        if ($gradoId && $gradoId !== 'all') {
            $query->where('grado_id', $gradoId);
        }

        if (!empty($programaIds)) {
            $query->whereIn('id', (array) $programaIds);
        }

        $programas = $query->get();

        $programasData = [];
        foreach ($programas as $programa) {
            $inscripciones = $programa->inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->ap_materno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->nombres ?? '');
            })->values();

            $docenteObj = app(\App\Services\DocenteService::class)->getDocenteEntrevistaForReport($programa->id, $programa->docenteEntrevista);

            if ($programa->id === 9) {
                $grupo1 = $inscripciones->take(30);
                $grupo2 = $inscripciones->slice(30)->take(25)->values();

                if ($grupo1->isNotEmpty()) {
                    $docenteG1 = new \stdClass();
                    $docenteG1->nombres = 'DR. CARLOS ADOLFO LOAYZA RIVAS';
                    $docenteG1->ap_paterno = '';
                    $docenteG1->ap_materno = '';
                    $docenteG1->dni = '';

                    $programasData[] = (object)[
                        'id' => $programa->id,
                        'nombre' => $programa->nombre,
                        'grado' => $programa->grado,
                        'inscripciones' => $grupo1,
                        'docente' => $docenteG1
                    ];
                }
                if ($grupo2->isNotEmpty()) {
                    $docenteG2 = new \stdClass();
                    $docenteG2->nombres = 'DR. JUAN FARIAS FEIJOO';
                    $docenteG2->ap_paterno = '';
                    $docenteG2->ap_materno = '';
                    $docenteG2->dni = '';

                    $programasData[] = (object)[
                        'id' => $programa->id,
                        'nombre' => $programa->nombre,
                        'grado' => $programa->grado,
                        'inscripciones' => $grupo2,
                        'docente' => $docenteG2
                    ];
                }
            } else {
                $programasData[] = (object)[
                    'id' => $programa->id,
                    'nombre' => $programa->nombre,
                    'grado' => $programa->grado,
                    'inscripciones' => $inscripciones,
                    'docente' => $docenteObj
                ];
            }
        }

        $pdf = Pdf::loadView('notas.postulantes-aptos-multiple', [
            'programas' => $programasData,
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
                    'id' => $idPrograma,
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
            21 => 'AULA 02',
            10 => 'AULA 03',
            34 => 'AULA 04',
            33 => 'AULA 05',
            8  => 'AULA 08',
            7  => 'AULA 09',
            22 => 'AULA 10',
            29 => 'AULA 11',
            31 => 'AULA 12',
            32 => 'AULA 13',
            25 => 'AULA 14',
            28 => 'AULA 15',
            27 => 'AULA 16',
            24 => 'AULA 17',
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
                ->where('estado', 1)
                ->whereHas('nota', function ($sq) {
                    $sq->whereNotNull('examen');
                })
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
                            'aula' => 'AULA 07',
                        ];
                    }

                    if ($inscripcionesGrupo2->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo2,
                            'docente' => $docente,
                            'aula' => 'AULA 06',
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
            21 => 'AULA 02',
            10 => 'AULA 03',
            34 => 'AULA 04',
            33 => 'AULA 05',
            8  => 'AULA 08',
            7  => 'AULA 09',
            22 => 'AULA 10',
            29 => 'AULA 11',
            31 => 'AULA 12',
            32 => 'AULA 13',
            25 => 'AULA 14',
            28 => 'AULA 15',
            27 => 'AULA 16',
            24 => 'AULA 17',
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
                ->where('estado', 1)
                ->whereHas('nota', function ($sq) {
                    $sq->whereNotNull('examen');
                })
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
                            'aula' => 'AULA 07',
                        ];
                    }

                    if ($inscripcionesGrupo2->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo2,
                            'docente' => $docente,
                            'aula' => 'AULA 06',
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

    public function generateComplementarioAsistenciaPdf()
    {
        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();

        $inscripciones = Inscripcion::with([
            'postulante',
            'programa.grado',
            'programa.docente',
            'nota'
        ])
            ->whereIn('programa_id', $idProgramas)
            ->where('estado', 1)
            ->whereDoesntHave('nota', function ($sq) {
                $sq->whereNotNull('examen');
            })
            ->get();

        if ($inscripciones->isEmpty()) {
            return response()->json(['error' => 'No hay postulantes registrados para el examen complementario'], 200);
        }

        $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
            return strtolower($inscripcion->postulante->ap_paterno ?? '') . ' ' .
                strtolower($inscripcion->postulante->ap_materno ?? '') . ' ' .
                strtolower($inscripcion->postulante->nombres ?? '');
        })->values();

        $pdf = Pdf::loadView('pdf.asistencia-complementario', ['inscripciones' => $inscripciones]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("reporte_asistencia_complementario_" . now()->format('d-m-Y_His') . ".pdf");
    }

    public function generateComplementarioEntrevistaPdf()
    {
        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();

        $programas = Programa::with([
            'grado',
            'docenteEntrevista',
            'inscripciones' => function ($q) {
                $q->where('estado', 1)
                    ->whereDoesntHave('nota', function ($sq) {
                        $sq->whereNotNull('entrevista');
                    })
                    ->with('postulante');
            }
        ])
            ->whereIn('id', $idProgramas)
            ->where('estado', 1)
            ->whereHas('inscripciones', function ($q) {
                $q->where('estado', 1)
                    ->whereDoesntHave('nota', function ($sq) {
                        $sq->whereNotNull('entrevista');
                    });
            })
            ->get();

        $programasData = [];
        foreach ($programas as $programa) {
            $inscripciones = $programa->inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->ap_materno ?? '') . ' ' .
                    strtolower($inscripcion->postulante->nombres ?? '');
            })->values();

            $docenteObj = app(\App\Services\DocenteService::class)->getDocenteEntrevistaForReport($programa->id, $programa->docenteEntrevista);

            $programasData[] = (object)[
                'id' => $programa->id,
                'nombre' => $programa->nombre,
                'grado' => $programa->grado,
                'inscripciones' => $inscripciones,
                'docente' => $docenteObj
            ];
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes registrados para la entrevista complementaria'], 200);
        }

        $pdf = Pdf::loadView('notas.postulantes-aptos-multiple-complementario', [
            'programas' => $programasData,
            'fechaHora' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("plantilla_entrevista_complementaria_" . now()->format('d-m-Y_His') . ".pdf");
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

    /**
     * Generar Excel de ingresantes por programa
     */
    public function generateIngresantesProgramaExcel()
    {
        $nombreArchivo = 'reporte_ingresantes_programa_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new IngresantesProgramaExport, $nombreArchivo);
    }

    public function generateAulasResumenPdf()
    {
        try {
            $aulasAsignadas = [
                21 => 'AULA 02',
                10 => 'AULA 03',
                34 => 'AULA 04',
                33 => 'AULA 05',
                8  => 'AULA 08',
                7  => 'AULA 09',
                22 => 'AULA 10',
                29 => 'AULA 11',
                31 => 'AULA 12',
                32 => 'AULA 13',
                25 => 'AULA 14',
                28 => 'AULA 15',
                27 => 'AULA 16',
                24 => 'AULA 17',
            ];

            // Obtener programas con estado 1 (aperturados)
            $programas = \App\Models\Programa::where('estado', 1)
                ->with(['grado'])
                ->get();

            $datosReporte = [];
            $subtotalInscritos = 0;
            $totalGeneralInscritos = 0;
            $cantidadProgramasConAula = 0;

            foreach ($programas as $p) {
                $idPrograma = $p->id;

                // Determinar aulas asignadas
                $aulas = [];
                $tieneAula = false;
                if ($idPrograma === 9) {
                    $aulas = ['AULA 06', 'AULA 07'];
                    $tieneAula = true;
                } elseif (isset($aulasAsignadas[$idPrograma])) {
                    $aulas = [$aulasAsignadas[$idPrograma]];
                    $tieneAula = true;
                } else {
                    $aulas = ['POR ASIGNAR'];
                }

                // Contar el número de inscritos (estado = 1)
                $inscritosCount = $p->inscripciones()->where('estado', 1)->count();

                $datosReporte[] = [
                    'grado' => $p->grado->nombre ?? 'Desconocido',
                    'programa' => $p->nombre,
                    'inscritos' => $inscritosCount,
                    'aulas' => implode(', ', $aulas),
                    'tiene_aula' => $tieneAula,
                ];

                if ($tieneAula) {
                    $subtotalInscritos += $inscritosCount;
                    $cantidadProgramasConAula++;
                }
                $totalGeneralInscritos += $inscritosCount;
            }

            // Ordenar en orden del número de inscritos (descendente)
            usort($datosReporte, function ($a, $b) {
                return $b['inscritos'] <=> $a['inscritos'];
            });

            // Cargar la vista y generar el PDF
            $pdf = Pdf::loadView('pdf.resumen-aulas', [
                'datos' => $datosReporte,
                'subtotalInscritos' => $subtotalInscritos,
                'totalGeneralInscritos' => $totalGeneralInscritos,
                'cantidadProgramasConAula' => $cantidadProgramasConAula,
                'totalProgramas' => count($programas),
            ]);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("resumen_inscritos_aulas_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF de resumen de aulas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateEvaluadoresPdf()
    {
        try {
            $aulasAsignadas = [
                21 => 'AULA 02',
                10 => 'AULA 03',
                34 => 'AULA 04',
                33 => 'AULA 05',
                8  => 'AULA 08',
                7  => 'AULA 09',
                22 => 'AULA 10',
                29 => 'AULA 11',
                31 => 'AULA 12',
                32 => 'AULA 13',
                25 => 'AULA 14',
                28 => 'AULA 15',
                27 => 'AULA 16',
                24 => 'AULA 17',
            ];

            // Listado de evaluadores de entrevista según el aula (del listado proporcionado por el usuario)
            $evaluadoresEspeciales = [
                21 => 'DRA. JESUS ALICIA FERNANDEZ PALOMINO / DR. FREDDY HERNANDEZ RENGIFO', // Aula 2
                10 => 'DR. LUIS ALBERTO OTAKE OYAMA', // Aula 3
                34 => 'DRA. MARIA JULIA JARAMILLO CARRION', // Aula 4
                33 => 'DR. MARIANO AGUSTIN RAMOS GARCIA / DR. ELEAZAR RUFASTO CAMPOS', // Aula 5
                8  => 'DRA. MARIANELLA LAURA GARCIA AURICH', // Aula 8
                7  => 'DR. HAMILTON CUEVA CAMPOS', // Aula 9
                22 => 'DR. LEOPOLDO YZQUIERDO HERNANDEZ', // Aula 10
                29 => 'DR. JOSE REUPO PERICHE', // Aula 11
                31 => 'M.SC. JOSE CARLOS LEIVA PIEDRA', // Aula 12
                32 => 'DR. VICTOR GUSTAVO HERNANDEZ JIMENEZ', // Aula 13
                25 => 'DRA. MILAGROS DEL PILAR CABEZAS MARTINEZ', // Aula 14
                28 => 'DR. PERCY MORANTE GAMARRA', // Aula 15
                27 => 'DR. JUAN CARLOS GRANADOS BARRETO', // Aula 16
                24 => 'DRA. GLORIA PUICON CRUZALEGUI', // Aula 17
            ];

            // Obtener programas con estado 1 (aperturados)
            $programas = \App\Models\Programa::where('estado', 1)
                ->with(['grado', 'docenteEntrevista'])
                ->get();

            $datosReporte = [];

            foreach ($programas as $p) {
                $idPrograma = $p->id;

                // Determinar aulas y evaluadores asignados
                $aulasData = [];
                if ($idPrograma === 9) {
                    $aulasData[] = [
                        'aula' => 'AULA 07',
                        'evaluador' => 'DR. CARLOS ADOLFO LOAYZA RIVAS'
                    ];
                    $aulasData[] = [
                        'aula' => 'AULA 06',
                        'evaluador' => 'DR. JUAN FARIAS FEIJOO'
                    ];
                } else {
                    $aula = $aulasAsignadas[$idPrograma] ?? null;
                    if ($aula) {
                        // Usar relación docenteEntrevista en base de datos si está poblado
                        $evaluador = 'POR ASIGNAR';
                        if ($p->docenteEntrevista) {
                            $evaluador = trim("{$p->docenteEntrevista->nombres} {$p->docenteEntrevista->ap_paterno} {$p->docenteEntrevista->ap_materno}");
                        } elseif (isset($evaluadoresEspeciales[$idPrograma])) {
                            $evaluador = $evaluadoresEspeciales[$idPrograma];
                        }
                        
                        $aulasData[] = [
                            'aula' => $aula,
                            'evaluador' => mb_strtoupper($evaluador, 'UTF-8')
                        ];
                    }
                }

                // Si no tiene aula asignada, no se considera en el reporte
                if (empty($aulasData)) {
                    continue;
                }

                // Contar el número de inscritos (estado = 1)
                $inscritosCount = $p->inscripciones()->where('estado', 1)->count();

                foreach ($aulasData as $ad) {
                    $inscritosAula = $inscritosCount;
                    if ($idPrograma === 9) {
                        if ($ad['aula'] === 'AULA 07') {
                            $inscritosAula = 30;
                        } else {
                            $inscritosAula = 25;
                        }
                    }

                    $datosReporte[] = [
                        'grado' => $p->grado->nombre ?? 'Desconocido',
                        'programa' => $p->nombre,
                        'inscritos' => $inscritosAula,
                        'aulas' => $ad['aula'],
                        'evaluador' => $ad['evaluador'],
                    ];
                }
            }

            // Ordenar por aula
            usort($datosReporte, function ($a, $b) {
                return strnatcmp($a['aulas'], $b['aulas']);
            });

            // Cargar la vista y generar el PDF
            $pdf = Pdf::loadView('pdf.resumen-evaluadores', ['datos' => $datosReporte]);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("resumen_evaluadores_aulas_" . now()->format('d-m-Y_His') . ".pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF de resumen de evaluadores.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
