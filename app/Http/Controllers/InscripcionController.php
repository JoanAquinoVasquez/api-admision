<?php

namespace App\Http\Controllers;

use App\DTOs\InscripcionData;
use App\Http\Requests\StoreInscripcionRequest;
use App\Models\Inscripcion;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Services\InscripcionService;
use Exception;
use Illuminate\Http\Request;

use App\Repositories\InscripcionRepository;

use App\Http\Resources\InscripcionResource;

class InscripcionController extends BaseController
{
    public function __construct(
        protected InscripcionService $inscripcionService,
        protected InscripcionRepositoryInterface $inscripcionRepository,
        protected \App\Services\ReportService $reportService,
        protected \App\Services\ValidationService $validationService
    ) {
    }

    /**
     * Método para obtener todos los registros
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $inscripciones = $this->inscripcionRepository->getAllWithRelations();
            return $this->successResponse(InscripcionResource::collection($inscripciones));
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

        try {
            $programasPosibles = $this->inscripcionService->getProgramasPosibles($id);
            // Asegurarnos de cargar la relación grado para extraer los grados únicos
            $programasPosibles->load('grado');
            $gradosPosibles = $programasPosibles->pluck('grado')->unique('id')->values();
        } catch (\Exception $e) {
            $programasPosibles = [];
            $gradosPosibles = [];
        }

        $data = $inscripcion->toArray();
        $data['programas_posibles'] = $programasPosibles;
        $data['grados_posibles'] = $gradosPosibles;

        return response()->json($data, 200);
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

        $result = $this->validationService->validateDigital(
            $validated['id'],
            $validated['tipoVal'],
            $validated['observacion'] ?? null
        );
        return response()->json($result);
    }


    public function reportProgramasNoAperturadosPDF()
    {
        return $this->reportService->generateProgramasNoAperturadosPDF();
    }


    public function reportProgramasAperturadosPDF()
    {
        return $this->reportService->generateProgramasAperturadosPDF();
    }

    // Reporte diario de inscritos por facultad en pdf
    public function reportFacultadPDF()
    {
        return $this->reportService->generateFacultadPDF();
    }

    /**
     * Validar físicamente una inscripción
     */
    public function valFisica($id)
    {
        return $this->handleRequest(function () use ($id) {
            $result = $this->validationService->validateFisica($id);
            return $this->successResponse($result);
        }, 'Error al validar físicamente la inscripción');
    }

    /**
     * Enviar correo de validación digital y constancia de postulante
     */
    public function enviarCorreo($id)
    {
        return $this->handleRequest(function () use ($id) {
            $result = $this->validationService->sendValidationEmail($id);
            return $this->successResponse(null, $result['message']);
        }, 'Error al enviar el correo de validación');
    }

    /**
     * Obtener programas posibles para el voucher de la inscripción
     */
    public function programasPosibles($id)
    {
        return $this->handleRequest(function () use ($id) {
            $programas = $this->inscripcionService->getProgramasPosibles($id);
            return $this->successResponse($programas);
        }, 'Error al obtener programas posibles');
    }

    /**
     * Generar reporte general de inscripciones (Excel)
     */
    public function report(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $gradoId = intval($request->input('grado'));
            $programaId = intval($request->input('programa'));

            $this->logActivity('Reporte de inscripciones generado', null, [
                'grado_id' => $gradoId,
                'programa_id' => $programaId,
            ]);

            return $this->reportService->generateInscripcionReport($gradoId, $programaId);
        }, 'Error al generar el reporte de inscripciones');
    }

    /**
     * Generar reporte diario de inscripciones (Excel)
     */
    public function reportDiario()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte diario de inscripciones generado');
            return $this->reportService->generateDailyReport();
        }, 'Error al generar el reporte diario');
    }

    /**
     * Generar reporte diario de inscritos por facultad (Excel)
     */
    public function reportDiarioFacultad()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte diario por facultad generado');
            return $this->reportService->generateFacultadReport();
        }, 'Error al generar el reporte por facultad');
    }

    /**
     * Generar reporte de programas top (PDF)
     */
    public function reportProgramasTop()
    {
        return $this->handleRequest(function () {
            $this->logActivity('Reporte de programas top generado');
            return $this->reportService->generateProgramasTopPDF();
        }, 'Error al generar el reporte de programas top');
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
        return $this->reportService->generatePreinscritosSinPagarReport();
    }

    public function resumenInscripcion()
    {
        return $this->handleRequest(function () {
            $result = $this->reportService->getResumenInscripcionData();
            return $this->successResponse($result);
        }, 'Error al obtener el resumen de inscripciones');
    }

    public function estadoInscripcion()
    {
        return $this->handleRequest(function () {
            $result = $this->reportService->getEstadoInscripcionData();
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
        return $this->reportService->generateFinalReportExcel();
    }

    public function reportNotasFinalExcel()
    {
        return $this->reportService->generateNotasFinalReportExcel();
    }

    public function reportFinalPdf()
    {
        return $this->reportService->generateFinalPdf();
    }

    public function reportFinalAulasPdf()
    {
        return $this->reportService->generateFinalAulasPdf();
    }

    public function reportFinalFirmasPdf()
    {
        return $this->reportService->generateFinalFirmasPdf();
    }

    public function resumenGeneralInscripcion()
    {
        return response()->json($this->reportService->getResumenGeneralInscripcion());
    }

    public function resumenInscripcionGrafico()
    {
        $inscripciones = $this->reportService->getResumenInscripcionGraficoData();
        return $this->successResponse($inscripciones);
    }
}
