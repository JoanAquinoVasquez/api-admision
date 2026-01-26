<?php

namespace App\Exports;

use App\Models\Documento;
use App\Models\Inscripcion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DevolucionExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        $inscripciones = Inscripcion::where('estado', 3)->get();

        return $inscripciones->map(function ($inscripcion) {
            // Verificar si el postulante tiene un documento de tipo 'Voucher'
            $documento = Documento::where([
                'tipo' => 'Voucher',
                'postulante_id' => $inscripcion->postulante_id
            ])->first();

            return [
                'ID' => $inscripcion->id,
                'N. Identidad' => $inscripcion->postulante->num_iden,
                'Nombres Completo' => $inscripcion->postulante->nombres . ' ' .
                    $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno,
                'Correo' => $inscripcion->postulante->email,
                'Celular' => $inscripcion->postulante->celular,
                'Grado' => $inscripcion->programa->grado->nombre,
                'Programa' => $inscripcion->programa->nombre,
                'N. Voucher' => $inscripcion->codigo,
                'URL Voucher' => $documento ? $documento->url : 'No disponible', // Evitar error si no hay documento
                'Estado' => 'Devolución',
                'Fecha de Inscripción' => $inscripcion->created_at,
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo'); // Obtener el periodo de admisión desde el archivo .env
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE POSTULANTES PARA DEVOLUCIÓN - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo"; // Encabezado especial
        return [
            [$programaHeading], // Primera fila con encabezado especial
            [ // Segunda fila con encabezados estándar
                'ID',
                'N. Identidad',
                'Nombres Completo',
                'Correo',
                'Telefono',
                'Grado',
                'Programa',
                'N. Voucher',
                'URL Voucher',
                'Estado',
                'Fecha de Inscripción',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustar el formato de texto a la izquierda para todas las celdas
        $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:K1'); // Fusionar celdas desde A1 hasta L1
        $sheet->setAutoFilter('A2:K2'); // Aplicar estilo a la primera fila (encabezados)
        $sheet->getStyle('A1:K1')->applyFromArray([
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

        $sheet->getStyle('A2:K2')->applyFromArray([
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
        $lastColumnIndex = 'K'; // Última columna a la que deseas aplicar el ajuste

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
