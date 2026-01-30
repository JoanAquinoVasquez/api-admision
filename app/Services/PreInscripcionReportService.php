<?php

namespace App\Services;

use App\Exports\PreInscripcionDiarioExport;
use App\Exports\PreInscripcionDiarioFacultadExport;
use App\Exports\PreInscripcionExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PreInscripcionReportService
{
    /**
     * Generate Excel report for pre-inscripciones
     */
    public function generatePreInscripcionReport(int $gradoId, int $programaId): BinaryFileResponse
    {
        $nombreArchivo = 'preinscripcion_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new PreInscripcionExport($gradoId, $programaId), $nombreArchivo);
    }

    /**
     * Generate daily report
     */
    public function generateDailyReport(): BinaryFileResponse
    {
        $nombreArchivo = 'preinscripcion_diario_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new PreInscripcionDiarioExport, $nombreArchivo);
    }

    /**
     * Generate daily faculty report
     */
    public function generateDailyFacultyReport(): BinaryFileResponse
    {
        $nombreArchivo = 'preinscripcion_facultad_diario_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new PreInscripcionDiarioFacultadExport, $nombreArchivo);
    }
}
