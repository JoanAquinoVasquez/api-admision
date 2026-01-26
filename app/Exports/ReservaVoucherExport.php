<?php

namespace App\Exports;

use App\Models\Inscripcion;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReservaVoucherExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Recuperar inscripciones con estado 2 (Reservado)
        $inscripciones = Inscripcion::where('estado', 2)
            ->with('voucher') // Cargar los datos relacionados de la tabla voucher
            ->get();

        // Crear un array para almacenar los datos que se exportarán
        $vouchers = $inscripciones->map(function ($inscripcion) {
            return [
                'Concepto de Pago' => $inscripcion->voucher->conceptoPago->cod_concepto,
                'Número Voucher' => $inscripcion->voucher->numero,
                'Número de Identidad' => $inscripcion->voucher->num_iden,
                'Nombre Completo' => $inscripcion->voucher->nombre_completo,
                'Monto' => $inscripcion->voucher->monto,
                'Fecha de Pago' => $inscripcion->voucher->fecha_pago,
                'Hora de Pago' => $inscripcion->voucher->hora_pago,
                'Cajero' => $inscripcion->voucher->cajero,
                'Agencia' => $inscripcion->voucher->agencia,
            ];
        });

        // Devolver los datos como una colección
        return collect($vouchers);
    }
}
