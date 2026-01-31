<?php

namespace App\Services;

use App\Exports\DevolucionExport;
use App\Exports\ReservaExport;
use App\Models\Documento;
use App\Models\Grado;
use App\Models\Inscripcion;
use App\Models\Programa;
use Carbon\Carbon;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReservaDevolucionService
{
    /**
     * Disable inscriptions for given programs
     */
    public function inhabilitarInscripciones(array $programaIds)
    {
        $programas = Programa::whereIn('id', $programaIds)->get();

        if ($programas->isEmpty()) {
            return null;
        }

        $programasInhabilitados = collect();
        foreach ($programas as $programa) {
            if ($programa->estado != 0) {
                $programa->estado = 0;
                $programa->save();
                $programasInhabilitados->push($programa);
            }
        }

        $idsProgramasInhabilitados = $programasInhabilitados->pluck('id');
        $inscripciones = Inscripcion::whereIn('programa_id', $idsProgramasInhabilitados)->get();

        $inscripcionesInhabilitadas = collect();
        foreach ($inscripciones as $inscripcion) {
            if ($inscripcion->estado != 0) {
                $inscripcion->estado = 0;
                $inscripcion->save();
                $inscripcionesInhabilitadas->push($inscripcion);
            }
        }

        return [
            'programas_inhabilitados' => $programasInhabilitados,
            'inscripciones_inhabilitadas' => $inscripcionesInhabilitadas,
        ];
    }

    /**
     * Get disabled programs
     */
    public function getProgramasInhabilitados(?int $gradoId = null)
    {
        $query = Programa::where('estado', 0);

        if ($gradoId) {
            $query->where('grado_id', $gradoId);
        }

        return $query->get();
    }

    /**
     * Get disabled inscriptions
     */
    public function getInscripcionesInhabilitadas(?int $programaId = null)
    {
        $query = Inscripcion::whereIn('estado', [0, 2, 3])
            ->with(['postulante', 'programa.grado']);

        if ($programaId) {
            $query->where('programa_id', $programaId);
        }

        return $query->get();
    }

    /**
     * Reserve an inscription
     */
    public function reservarInscripcion(int $inscripcionId)
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion) {
            return null;
        }

        $inscripcion->estado = 2;
        $inscripcion->save();

        return $inscripcion;
    }

    /**
     * Get reserved inscriptions
     */
    public function getInscripcionesReserva(int $programaId)
    {
        return Inscripcion::where(['estado' => 2, 'programa_id' => $programaId])->get();
    }

    /**
     * Cancel reservation
     */
    public function cancelarReserva(int $inscripcionId)
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion) {
            return null;
        }

        $inscripcion->estado = 0;
        $inscripcion->save();

        return $inscripcion;
    }

    /**
     * Generate reservation report
     */
    public function generateReportReserva()
    {
        $nombreArchivo = 'reporte_reservas_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new ReservaExport, $nombreArchivo);
    }

    /**
     * Generate reservation vouchers TXT file
     */
    public function generateReservaVouchersTxt()
    {
        $inscripciones = Inscripcion::where('estado', 2)
            ->with('voucher')
            ->get();

        $contenido = "";
        foreach ($inscripciones as $inscripcion) {
            $voucher = $inscripcion->voucher;

            $line =
                str_pad('', 18) .
                str_pad($voucher->numero, 7, '0', STR_PAD_LEFT) .
                str_pad('', 10) .
                str_pad($voucher->conceptoPago->cod_concepto, 8, '0', STR_PAD_LEFT) .
                str_pad('', 11) .
                str_pad($voucher->num_iden, 8, '0', STR_PAD_LEFT) .
                str_pad(number_format($voucher->monto * 100, 0, '', ''), 15, '0', STR_PAD_LEFT) .
                str_pad('00', 2) .
                date('Ymd', strtotime($voucher->fecha_pago)) .
                date('His', strtotime($voucher->hora_pago)) .
                str_pad($voucher->cajero, 4, '0', STR_PAD_LEFT) .
                str_pad($voucher->agencia, 4, '0', STR_PAD_LEFT) .
                str_pad('', 20) .
                str_pad($voucher->nombre_completo, 35, ' ', STR_PAD_RIGHT) .
                str_pad('0301029403', 10, ' ', STR_PAD_LEFT) .
                "\n";

            $contenido .= $line;
        }

        return $contenido;
    }

    /**
     * Mark inscription for refund
     */
    public function devolverInscripcion(int $inscripcionId)
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion) {
            return null;
        }

        if ($inscripcion->estado == 1) {
            return false; // Program is active
        }

        $inscripcion->estado = 3;
        $inscripcion->save();

        return $inscripcion;
    }

    /**
     * Get inscriptions marked for refund
     */
    public function getInscripcionesDevolver(int $programaId)
    {
        return Inscripcion::where(['estado' => 3, 'programa_id' => $programaId])->get();
    }

    /**
     * Cancel refund
     */
    public function cancelarDevolucion(int $inscripcionId)
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion || $inscripcion->estado == 1) {
            return null;
        }

        $inscripcion->estado = 0;
        $inscripcion->save();

        return $inscripcion;
    }

    /**
     * Generate refund report
     */
    public function generateReportDevolucion()
    {
        $nombreArchivo = 'reporte_devolucion_' . now()->format('d-m-Y_His') . '.xlsx';
        return Excel::download(new DevolucionExport, $nombreArchivo);
    }

    /**
     * Get possible programs for program change
     */
    public function getProgramasPosibles(int $inscripcionId)
    {
        $inscripcion = Inscripcion::find($inscripcionId);

        if (!$inscripcion) {
            return null;
        }

        $concepto_pago_id = $inscripcion->voucher->concepto_pago_id;

        $programas_posibles = Programa::where('concepto_pago_id', $concepto_pago_id)
            ->where('estado', true)
            ->get();

        $grados_posibles = Grado::whereIn('id', $programas_posibles->pluck('grado_id'))
            ->get();

        return [
            'grados_posibles' => $grados_posibles,
            'programas_posibles' => $programas_posibles,
        ];
    }

    /**
     * Update program for an inscription
     */
    public function updatePrograma(int $inscripcionId, int $nuevoProgramaId, $request = null)
    {
        $inscripcion = Inscripcion::find($inscripcionId);
        $programa_old = Programa::find($inscripcion->programa_id);
        $old_grado = $inscripcion->programa->grado_id;

        Log::info("🔄 updatePrograma: Inscripcion $inscripcionId, OldProgram {$programa_old->id}, NewProgram $nuevoProgramaId");

        if (!$inscripcion) {
            return null;
        }

        $programa = Programa::find($nuevoProgramaId);

        if ($programa->estado == 0) {
            return false; // Program not enabled
        }

        $inscripcion->programa_id = $nuevoProgramaId;
        $inscripcion->estado = 1;
        $inscripcion->save();
        $inscripcion->load('programa');

        $new_grado = $inscripcion->programa->grado_id;

        Log::info("📝 Checking Grade Change: Old $old_grado vs New $new_grado");

        // Move documents if grade changed
        if ($old_grado != $new_grado) {
            Log::info("🚀 Grades different, dispatching job to move documents...");

            // Dispatch logic used in InscripcionUpdateController
            \App\Jobs\MoverDocumentosGoogleDriveJob::dispatch(
                $inscripcion->postulante_id,
                $new_grado,
                [] // No files are being replaced in this action
            )->afterCommit();

            Log::info("✅ Job MoverDocumentosGoogleDriveJob dispatched.");
        } else {
            Log::info("ℹ️ Grades are same, skipping document move.");
        }

        return [
            'inscripcion' => $inscripcion,
            'programa_old' => $programa_old,
            'programa_new' => $programa,
        ];
    }
}
