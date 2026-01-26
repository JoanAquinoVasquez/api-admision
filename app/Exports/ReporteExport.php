<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ReporteExport implements FromCollection, WithStyles, WithTitle, WithColumnFormatting
{
    protected $empleadoTipos;
    protected $logoPath;

    public function __construct($empleadoTipos)
    {
        $this->empleadoTipos = $empleadoTipos;
        $this->logoPath = public_path('isotipo_color.webp'); // Ruta del logo
    }

    // 1. Datos de la colección
    public function collection()
    {
        // Filas vacías para las primeras 11 filas
        $emptyRows = collect(array_fill(0, 5, [
            '', '', '', '', '', '', '', '', ''
        ]));

        // Encabezados en la fila 12
        $headers = collect([[
            'Tipo Doc.',
            'N° Doc.',
            'Nombre Completo',
            'Unidad',
            'Facultad',
            'Escuela',
            'Tipo',
            'CCI',
            'Cuenta',
        ]]);

        // Datos mapeados
        $data = $this->empleadoTipos->map(function ($empleadoTipo) {
            $tipoEmpleado = match (strtolower($empleadoTipo->tipoEmpleado->nombre)) {
                'practicante' => 'PRA',
                'docente' => 'DOC',
                'administrativo' => 'ADMIN',
                default => 'OTRO',
            };

            $subTipoEmpleado = match (strtolower($empleadoTipo->subTipoEmpleado->nombre)) {
                'preprofesional' => 'PRE',
                'profesional' => 'PRO',
                default => 'OTRO',
            };

            $tipoSubtipo = "{$tipoEmpleado}-{$subTipoEmpleado}";

            return [
                $empleadoTipo->empleado->tipo_doc_iden,
                $empleadoTipo->empleado_num_doc_iden,
                $empleadoTipo->empleado->apellido_paterno . ' ' . 
                $empleadoTipo->empleado->apellido_materno . ' ' . 
                $empleadoTipo->empleado->nombres,
                $empleadoTipo->areaActiva?->area?->oficina,
                $empleadoTipo->areaActiva?->area?->facultad,
                $empleadoTipo->areaActiva?->area?->escuela,
                $tipoSubtipo,
                $empleadoTipo->cci,
                $empleadoTipo->numero_cuenta,
            ];
        });

        // Combinar filas vacías, encabezados y datos
        return $emptyRows->concat($headers)->concat($data);
    }

    // 2. Formatear columnas específicas como número sin decimales
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER, // CCI
            'I' => NumberFormat::FORMAT_NUMBER, // Número de Cuenta
        ];
    }

    // 3. Estilos y añadir la imagen
    public function styles(Worksheet $sheet)
    {
        // Añadir imagen desde la fila 1 hasta la fila 11
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la empresa');
        $drawing->setPath($this->logoPath); // Ruta de la imagen
        $drawing->setHeight(80); // Altura de la imagen (ajustar según necesidad)
        $drawing->setCoordinates('A1'); // Comienza en A1
        $drawing->setWorksheet($sheet);
        $drawing->setOffsetY(10);
    
        // Título del reporte
        $sheet->setCellValue('E3', 'REPORTE DE PRACTICANTES');
        $sheet->getStyle('E3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '1D5181'], // Color azul oscuro
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    
        // Fecha y hora
        $currentDateTime = Carbon::now()->format('d/m/Y H:i');
        $sheet->setCellValue('I3', 'Fecha y Hora: ' . $currentDateTime);
        $sheet->getStyle('I3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'italic' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
    
        // Encabezado de la tabla
        $sheet->getStyle('A6:I6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D5181'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);
    
        // Activar el filtro en el encabezado de la tabla
        $sheet->setAutoFilter('A6:I6');
    
        // Aplicar bordes y estilo zebra a las filas de datos
        $highestRow = $sheet->getHighestRow(); // Obtener la última fila con datos
        for ($row = 7; $row <= $highestRow; $row++) {
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
    
            // Alternar color de fondo para estilo zebra
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
                ]);
            }
        }
    
        // Ajustar el ancho de las columnas automáticamente según el contenido
        foreach (range('A', 'I') as $column) {
            if ($column === 'E') { // Columna "Unidad"
                $sheet->getColumnDimension($column)->setWidth(15); // Ancho fijo para la columna "Unidad"
            } else {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
    
        // Cambiar la altura de la fila del encabezado para mayor visibilidad
        $sheet->getRowDimension(6)->setRowHeight(25);
    }

    // 4. Nombre de la hoja
    public function title(): string
    {
        return 'Reporte Practicantes'; // Cambia el nombre de la hoja
    }
}
