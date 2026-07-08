<?php

namespace App\Exports;

use App\Models\Programa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class IngresantesProgramaExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
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

                return [
                    'facultad' => mb_strtoupper($programa->facultad ? $programa->facultad->nombre : 'N/A'),
                    'grado' => mb_strtoupper($programa->grado ? $programa->grado->nombre : 'N/A'),
                    'programa' => mb_strtoupper($programa->nombre),
                    'total_ingresantes' => $ingresantes->count(),
                ];
            })
            ->sortByDesc('total_ingresantes')
            ->values();

        $reportData = new Collection();
        $contador = 1;

        foreach ($programas as $p) {
            $reportData->push([
                'N°' => $contador++,
                'FACULTAD' => $p['facultad'],
                'GRADO ACADÉMICO' => $p['grado'],
                'PROGRAMA ACADÉMICO' => $p['programa'],
                'TOTAL' => $p['total_ingresantes'],
            ]);
        }

        return $reportData;
    }

    public function headings(): array
    {
        return [
            [
                'N°',
                'FACULTAD',
                'GRADO ACADÉMICO',
                'PROGRAMA ACADÉMICO',
                'TOTAL'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $fechaHora = now()->format('d/m/Y H:i:s');

        // Insertar 6 filas al inicio para el título y la metadata
        $sheet->insertNewRowBefore(1, 6);

        // A1: Título del Reporte
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'REPORTE DE INGRESANTES POR PROGRAMA');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('003366'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata
        $sheet->setCellValue('A3', 'Fecha y hora:');
        $sheet->setCellValue('B3', $fechaHora);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'Rector:');
        $sheet->setCellValue('B4', 'Dr. Enrique Wilfredo Carpena Velásquez');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'Director de Escuela:');
        $sheet->setCellValue('B5', 'Dr. Leandro Agapito Aznarán Castillo');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        // Obtener la fila donde termina la tabla (encabezado en fila 7 + número de filas de datos)
        $highestRow = $sheet->getHighestRow();

        // Aplicar estilos a los encabezados de la tabla (fila 7)
        $sheet->getStyle('A7:E7')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '003366'], // Azul oscuro corporativo como en el PDF
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Alinear el contenido de la tabla
        $sheet->getStyle('A8:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C8:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E8:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Agregar fila total general
        $totalRow = $highestRow + 1;
        $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAL GENERAL DE INGRESANTES');
        $sheet->setCellValue("E{$totalRow}", "=SUM(E8:E{$highestRow})");

        $sheet->getStyle("A{$totalRow}:E{$totalRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '003366'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8ECF1'],
            ],
        ]);
        $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Aplicar bordes delgados a la tabla (filas 7 hasta totalRow)
        $sheet->getStyle("A7:E{$totalRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Agregar la firma al final (dejando 4 filas vacías)
        $firmaStart = $totalRow + 4;
        $sheet->mergeCells("B{$firmaStart}:D{$firmaStart}");
        $sheet->getStyle("B{$firmaStart}:D{$firmaStart}")->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $firmaNombre = $firmaStart + 1;
        $sheet->mergeCells("B{$firmaNombre}:D{$firmaNombre}");
        $sheet->setCellValue("B{$firmaNombre}", 'Dr. Leandro Agapito Aznarán Castillo');
        $sheet->getStyle("B{$firmaNombre}")->getFont()->setBold(true);
        $sheet->getStyle("B{$firmaNombre}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $firmaCargo = $firmaNombre + 1;
        $sheet->mergeCells("B{$firmaCargo}:D{$firmaCargo}");
        $sheet->setCellValue("B{$firmaCargo}", 'Director de la Escuela de Posgrado');
        $sheet->getStyle("B{$firmaCargo}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-ajustar ancho de columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
