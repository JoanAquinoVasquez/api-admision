<?php

namespace App\Exports;

use App\Models\Voucher;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VoucherExport implements FromCollection, WithHeadings, WithStyles
{

    // Resividos por el constructor
    public function __construct()
    {
    }

    public function collection()
    {
        $vouchers = Voucher::all();

        return $vouchers->map(function ($voucher) {
            return [
                'ID' => $voucher->id,
                'N. Identidad' => $voucher->num_iden,
                'Nombre Completo' => $voucher->nombre_completo,
                'N. Voucher' => $voucher->numero,
                'Codigo de Pago' => $voucher->conceptoPago->cod_concepto,
                'Concepto de Pago' => $voucher->conceptoPago->nombre,
                'Fecha' => $voucher->fecha_pago,
                'Hora' => $voucher->hora_pago,
                'Agencia' => $voucher->agencia,
                'Cajero' => $voucher->cajero,
                'Monto' => 'S/. ' . $voucher->monto,
                'Estado' => $voucher->estado ? 'Activo' : 'Usado',
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE VOUCHERS - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo"; // Encabezado especial
        return [
            [$programaHeading], // Primera fila con encabezado especial
            [ // Segunda fila con encabezados estándar
                'ID',
                'N. Identidad',
                'Nombre Completo',
                'N. Voucher',
                'Codigo de Pago',
                'Concepto de Pago',
                'Fecha',
                'Hora',
                'Agencia',
                'Cajero',
                'Monto',
                'Estado'
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustar el formato de texto a la izquierda para todas las celdas
        $sheet->getStyle('A:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:L1'); // Fusionar celdas desde A1 hasta L1
        $sheet->setAutoFilter('A2:L2'); // Aplicar estilo a la primera fila (encabezados)
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // Color de texto blanco
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff'], // Color de fondo azul
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER, // Centrar verticalmente el contenido
            ],
        ]);

        $sheet->getStyle('A2:L2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // Color de texto blanco
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff'], // Color de fondo azul
            ],
        ]);

        // Obtener los encabezados de la segunda fila (encabezados estándar)
        $standardHeadings = $this->headings()[1];

        $columnIndex = 'A';
        $lastColumnIndex = 'L'; // Última columna a la que deseas aplicar el ajuste

        while ($columnIndex <= $lastColumnIndex) {
            $dimension = $sheet->getColumnDimension($columnIndex);

            $dimension->setAutoSize(true);

            // Ajustar el ancho de la columna comprimiéndola ligeramente
            $heading = $standardHeadings[ord($columnIndex) - ord('A')]; // Obtener el encabezado para la columna actual
            $dimension->setWidth(strlen($heading) + 4); // Ajustar según la longitud del encabezado

            $columnIndex++;
        }
    }
}
