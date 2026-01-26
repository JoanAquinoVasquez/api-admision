<?php

namespace App\Services;

use App\Exports\InscripcionDiarioExport;
use App\Exports\InscripcionDiarioFacultadExport;
use App\Exports\InscripcionesFinalesExport;
use App\Exports\InscripcionExport;
use App\Exports\InscripcionNotasFinalExport;
use App\Exports\PreinscripcionSinPagarExport;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
        $nombreArchivo = 'reporte_inscripcion_' . Carbon::now()->format('His_dmy') . '.xlsx';
        return Excel::download(new InscripcionExport($gradoId, $programaId), $nombreArchivo);
    }

    /**
     * Generar reporte diario de inscripciones
     */
    public function generateDailyReport()
    {
        $nombreArchivo = 'reporte_inscripcion_diario_' . Carbon::now()->format('His_dmy') . '.xlsx';
        return Excel::download(new InscripcionDiarioExport, $nombreArchivo);
    }

    /**
     * Generar reporte diario de inscritos por facultad
     */
    public function generateFacultadReport()
    {
        $nombreArchivo = 'reporte_inscripcion_diario_facultad_' . Carbon::now()->format('His_dmy') . '.xlsx';
        return Excel::download(new InscripcionDiarioFacultadExport, $nombreArchivo);
    }

    /**
     * Generar reporte de preinscritos sin pagar
     */
    public function generatePreinscritosSinPagarReport()
    {
        $nombreArchivo = 'reporte_pre-inscripcion_sin_pagar' . Carbon::now()->format('His_dmy') . '.xlsx';
        return Excel::download(new PreinscripcionSinPagarExport, $nombreArchivo);
    }

    /**
     * Generar reporte final en Excel
     */
    public function generateFinalReportExcel()
    {
        $nombreArchivo = 'reporte_final_' . Carbon::now()->format('His_dmy') . '.xlsx';
        return Excel::download(new InscripcionesFinalesExport, $nombreArchivo);
    }

    /**
     * Generar reporte de notas finales en Excel
     */
    public function generateNotasFinalReportExcel()
    {
        $nombreArchivo = 'reporte_resultados_' . Carbon::now()->format('His_dmy') . '.xlsx';
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

        return $pdf->stream("reporte-inscripcion-top.pdf");
    }

    /**
     * Generar PDF de programas no aperturados
     */
    public function generateProgramasNoAperturadosPDF()
    {
        $programas = $this->programaRepository->all()
            ->where('estado', false)
            ->map(function ($programa) {
                return [
                    'id' => $programa->id,
                    'facultad' => $programa->facultad->siglas ?? 'N/A',
                    'grado' => $programa->grado->nombre ?? 'N/A',
                    'programa' => $programa->nombre,
                ];
            });

        $pdf = Pdf::loadView('reporte-programas-no-aperturados', [
            'programas' => $programas,
            'fechaHora' => now(),
        ]);

        return $pdf->stream("reporte-programas-no-aperturados.pdf");
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
}
