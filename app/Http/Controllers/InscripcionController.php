<?php

namespace App\Http\Controllers;

use App\DTOs\InscripcionData;
use App\Exports\InscripcionDiarioExport;
use App\Exports\InscripcionDiarioFacultadExport;
use App\Exports\InscripcionesFinalesExport;
use App\Exports\InscripcionExport;
use App\Exports\InscripcionNotasFinalExport;
use App\Exports\PreinscripcionSinPagarExport;
use App\Http\Requests\StoreInscripcionRequest;
use App\Jobs\SendEmailValidarInscripcionJob;
use App\Mail\InscripcionValidadaEmail;
use App\Models\ComisionAdmision;
use App\Models\Facultad;
use App\Models\Grado;
use App\Models\Inscripcion;
use App\Models\Programa;
use App\Models\Voucher;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Services\InscripcionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

use App\Repositories\InscripcionRepository;

class InscripcionController extends BaseController
{
    protected InscripcionService $inscripcionService;
    protected InscripcionRepositoryInterface $inscripcionRepository;

    public function __construct(
        InscripcionService $inscripcionService,
        InscripcionRepositoryInterface $inscripcionRepository
    ) {
        $this->inscripcionService = $inscripcionService;
        $this->inscripcionRepository = $inscripcionRepository;
    }

    /**
     * Método para obtener todos los registros
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $inscripciones = $this->inscripcionRepository->getAllWithRelations();
            return $this->successResponse($inscripciones);
        }, 'Error al obtener las inscripciones');
    }

    /**
     * Método para almacenar una nueva inscripción
     */
    public function store(StoreInscripcionRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $inscripcionData = InscripcionData::fromRequest($request);
            $resultado = $this->inscripcionService->storeInscripcion($inscripcionData);

            if ($resultado['success']) {
                $this->logActivity('Nueva inscripción registrada', null, $resultado['data'] ?? []);
                return $this->successResponse(
                    $resultado['data'] ?? [],
                    $resultado['message'],
                    201
                );
            }

            return $this->errorResponse($resultado['message'], 422);
        }, 'Error inesperado al procesar la inscripción');
    }

    /**
     * Método para mostrar una Inscripcion y los programa posibles segun el voucher que pago el postulante
     */
    public function show($id)
    {
        $inscripcion = $this->inscripcionRepository->findWithRelations($id);

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        return response()->json($inscripcion, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Método no implementado en refactorización actual'], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->inscripcionRepository->delete($id);
        return response()->json(['message' => 'Inscripción eliminada'], 200);
    }

    /**
     * Validar digitalmente una inscripción
     */
    public function valDigital(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:inscripcions,id',
            'tipoVal' => 'required|digits_between:0,2',
            'observacion' => 'required_if:tipoVal,2|string|max:255',
        ]);

        $result = app(\App\Services\ValidationService::class)->validateDigital(
            $validated['id'],
            $validated['tipoVal'],
            $validated['observacion'] ?? null
        );
        return response()->json($result);
    }


    public function reportProgramasNoAperturadosPDF()
    {
        try {
            // Obtener todos los programas activos con sus relaciones
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

            return $pdf->stream("reporte-programas-no-aperturados.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function reportProgramasAperturadosPDF()
    {
        try {
            // Obtener todos los programas activos con sus relaciones
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
            Log::info($programas);

            $pdf = Pdf::loadView('reporte-programas', [
                'programas' => $programas,
                'fechaHora' => now(),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("reporte-programas.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Reporte diario de inscritos por facultad en pdf
    public function reportFacultadPDF()
    {
        try {
            // Obtener los grados por facultad con sus programas y el total de inscritos
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

            // Pasar los datos a la vista
            $pdf = Pdf::loadView('reporte-inscripcion', [
                'facultades' => $facultades,
                'fechaHora' => now(),
            ]);

            // Definir nombre del archivo PDF
            $nombreArchivo = "reporte-inscripcion.pdf";

            // Configuración del tamaño de la página y orientación
            $pdf->setPaper('A4', 'portrait');

            // Renderizar PDF
            return $pdf->stream($nombreArchivo);
        } catch (\Exception $e) {
            // Manejar cualquier error que pueda ocurrir
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la constancia en PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function inscripcionNota()
    {
        try {
            $inscripcions = Inscripcion::with([
                'programa.grado',
                'programa.facultad',
                'postulante',
                'nota',
            ])->get();
            return response()->json($inscripcions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las inscripciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportPreinscritosSinPagar()
    {
        try {
            // Genera el nombre del archivo con la fecha y hora actual
            $nombreArchivo = 'reporte_pre-inscripcion_sin_pagar' . Carbon::now()->format('His_dmy') . '.xlsx';
            return Excel::download(new PreinscripcionSinPagarExport, $nombreArchivo);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al exporte el reporte de Pre-inscritos que aun no pagan su Derecho de Inscripcion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resumenInscripcion()
    {
        return $this->handleRequest(function () {
            $programas = Programa::with(['grado', 'facultad', 'conceptoPago', 'inscripciones'])->get();

            $result = $programas->map(function ($programa) {
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

            return $this->successResponse($result);
        }, 'Error al obtener el resumen de inscripciones');
    }

    public function estadoInscripcion()
    {
        return $this->handleRequest(function () {

            $inscripciones = Inscripcion::with([
                'programa.grado',
                'programa.facultad',
                'postulante.documentos',
                'postulante.distrito.provincia.departamento',
                'voucher.conceptoPago'
            ])/* ->where('estado', 1) */ ->get();

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

            $result = [
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

            return $this->successResponse($result);
        }, 'Error al obtener el estado de las inscripciones');
    }


    // El mismo reporte de arriba solo que en una sola tabla
    /* public function reportFacultadPDF()
    {
        try {
            // Obtener los grados por facultad con sus programas y el total de inscritos
            $facultades = Facultad::with(['programas.grado', 'programas.inscripciones'])
                ->get()
                ->map(function ($facultad) {
                    return $facultad->programas->map(function ($programa) use ($facultad) {
                        // Obtener el total de inscritos
                        $totalInscritos = $programa->inscripciones->count();

                        return (object)[
                            'facultad' => $facultad->nombre,
                            'grado' => $programa->grado ? $programa->grado->nombre : 'N/A', // Evitar error si no hay grado
                            'programa' => $programa->nombre,
                            'total_inscritos' => $totalInscritos,
                        ];
                    });
                })
                ->flatten();

            // Pasar los datos a la vista
            $pdf = Pdf::loadView('reporte-inscripcion', [
                'datos' => $facultades,
                'fechaHora' => now(),  // Fecha y hora actual
            ]);

            // Definir nombre del archivo PDF
            $nombreArchivo = "reporte-inscripcion.pdf";

            // Configuración del tamaño de la página y orientación
            $pdf->setPaper('A4', 'portrait');

            // Renderizar PDF
            return $pdf->stream($nombreArchivo);
        } catch (\Exception $e) {
            // Manejar cualquier error que pueda ocurrir
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la constancia en PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    } */

    public function reportFinalExcel()
    {
        try {
            // Genera el nombre del archivo con la fecha y hora actual
            $nombreArchivo = 'reporte_final_' . Carbon::now()->format('His_dmy') . '.xlsx';

            // Descarga el archivo Excel con el nombre generado
            return Excel::download(new InscripcionesFinalesExport, $nombreArchivo);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el reporte de inscripciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportNotasFinalExcel()
    {
        try {
            // Genera el nombre del archivo con la fecha y hora actual
            $nombreArchivo = 'reporte_resultados_' . Carbon::now()->format('His_dmy') . '.xlsx';

            // Descarga el archivo Excel con el nombre generado
            return Excel::download(new InscripcionNotasFinalExport, $nombreArchivo);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el reporte de inscripciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportFinalPdf()
    {
        $idProgramas = Programa::where('estado', 1)->pluck('id')->toArray();

        $programasData = [];

        foreach ($idProgramas as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota' // Cargar las notas de los postulantes
            ])
                ->where('programa_id', $idPrograma)
                // ->where('val_fisico', 1)  // Postulantes aptos
                ->get();

            // Ordenar por ap_paterno, ap_materno y nombres
            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values(); // Resetear índices del array

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

        // Generar el PDF con varias páginas
        $pdf = Pdf::loadView('postulante-aptos-final', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');
        $nombreArchivo = "notasCV-multiple.pdf";

        return $pdf->stream($nombreArchivo);
    }

    public function reportFinalAulasPdf()
    {
        // Asignación de aulas por ID de programa
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
                // ->where('val_fisico', 1)  // Postulantes aptos
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
                    'aula' => $aula, // <-- Aquí agregamos el AULA al array
                ];
            }
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        // Generar PDF con el array completo incluyendo aulas
        $pdf = Pdf::loadView('postulante-aptos-final-aulas', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');
        $nombreArchivo = "reporte_aulas.pdf";

        return $pdf->stream($nombreArchivo);

        /* $pdf = Pdf::loadView('postulante-aptos-final-aulas', ['programasData' => $programasData]);
        // return $pdf->stream($nombreArchivo);
        return view('postulante-aptos-final-aulas', ['programasData' => $programasData]); */
    }

    public function reportFinalFirmasPdf()
    {
        // Asignación de aulas por ID de programa
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
                    'aula' => $aula, // <-- Aquí agregamos el AULA al array
                ];
            }
        }

        if (empty($programasData)) {
            return response()->json(['error' => 'No hay postulantes aptos registrados para los programas seleccionados'], 200);
        }

        // Generar PDF con el array completo incluyendo aulas
        $pdf = Pdf::loadView('postulante-aptos-final-firmas', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');
        $nombreArchivo = "reporte_aptos_firmas.pdf";

        return $pdf->stream($nombreArchivo);

        /* $pdf = Pdf::loadView('postulante-aptos-final-aulas', ['programasData' => $programasData]);
        // return $pdf->stream($nombreArchivo);
        return view('postulante-aptos-final-aulas', ['programasData' => $programasData]); */
    }

    public function resumenGeneralInscripcion()
    {
        $programas = Programa::with(['grado', 'inscripciones', 'facultad'])->get();
        $comision = ComisionAdmision::all();
        $vouchers = Voucher::all();

        $resumen = [];

        foreach ($comision as $miembro) {
            // 🔹 Filtrar programas según el atributo resumen_completo
            $programasFiltrados = $miembro->resumen_completo
                ? $programas   // todos los programas
                : $programas->where('facultad_id', $miembro->facultad_id); // solo de su facultad

            $detalleProgramas = [];
            $totales = [];
            $totalGeneral = 0;

            foreach ($programasFiltrados as $programa) {
                $cantidad = $programa->inscripciones->count();
                $totalGeneral += $cantidad;

                // Abreviatura del grado
                $abreviatura_grado = match ($programa->grado->id) {
                    1 => 'DOC',
                    2 => 'MAE',
                    3 => 'SEG',
                    default => 'N/A'
                };

                // Guardar totales por grado (en mayúscula)
                $gradoNombre = strtoupper(trim($programa->grado->nombre));
                if (!isset($totales[$gradoNombre])) {
                    $totales[$gradoNombre] = 0;
                }
                $totales[$gradoNombre] += $cantidad;

                // Cobertura
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

            // Agregar total general
            $totales['TOTAL'] = $totalGeneral;

            $vouchersArray = [];

            // Totales de vouchers
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

        return response()->json($resumen);
    }

    public function resumenInscripcionGrafico()
    {
        $inscripciones = Inscripcion::with('programa.grado')
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

        return $this->successResponse($inscripciones);
    }
}
