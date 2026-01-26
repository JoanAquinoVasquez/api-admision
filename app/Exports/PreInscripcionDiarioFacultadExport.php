<?php

namespace App\Exports;

use App\Models\Facultad;
use App\Models\Grado;
use App\Models\PreInscripcion;
use App\Models\Programa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PreInscripcionDiarioFacultadExport implements FromArray, WithEvents, ShouldAutoSize, WithStyles
{
    protected $dates;
    protected $sortedGrados;

    public function __construct()
    {
        // 1. Obtener todas las fechas únicas de preinscripciones ordenadas
        $this->dates = PreInscripcion::selectRaw('DATE(created_at) as date')
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();

        // 2. Definir el orden de los grados académicos
        $this->sortedGrados = ['MAESTRIA', 'DOCTORADO', 'SEGUNDA ESPECIALIDAD PROFESIONAL'];
    }

    /**
     * Construye el array de datos para la exportación.
     *
     * @return array
     */
    public function array(): array
    {
        $data = [];
        $facultads = Facultad::where('estado', true)->orderBy('nombre')->get();

        foreach ($facultads as $facultad) {
            // Añadir encabezados para la facultad actual
            $header = array_merge(
                ['GRADOS ACADÉMICOS', 'FACULTAD', 'PROGRAMAS ACADEMICOS'],
                $this->formatDatesForHeadings(),
                ['ACUMULADO']
            );
            $data[] = $header;

            $totalAcumuladoFacultad = 0;

            foreach ($this->sortedGrados as $gradoNombre) {
                $grado = Grado::where('nombre', $gradoNombre)->first();
                if (!$grado) continue;

                $programas = Programa::where('facultad_id', $facultad->id)
                    ->where('grado_id', $grado->id)
                    ->where('estado', true)
                    ->orderBy('nombre')
                    ->get();

                foreach ($programas as $programa) {
                    $row = [
                        $gradoNombre,
                        $facultad->nombre,
                        $programa->nombre,
                    ];

                    $acumulado = 0;

                    foreach ($this->dates as $date) {
                        $count = PreInscripcion::where('programa_id', $programa->id)
                            ->whereDate('created_at', $date)
                            ->count();
                        $row[] = $count;
                        $acumulado += $count;
                    }

                    $row[] = $acumulado;
                    $totalAcumuladoFacultad += $acumulado;

                    $data[] = $row;
                }
            }

            // Añadir fila de TOTAL
            $totalRow = ['', '', 'TOTAL'];
            foreach ($this->dates as $date) {
                $sumPerDate = PreInscripcion::whereHas('programa', function ($query) use ($facultad) {
                    $query->where('facultad_id', $facultad->id);
                })
                    ->whereDate('created_at', $date)
                    ->count();
                $totalRow[] = $sumPerDate;
            }
            $totalRow[] = $totalAcumuladoFacultad;
            $data[] = $totalRow;

            // Añadir una fila en blanco entre tablas
            $data[] = [''];
        }

        return $data;
    }

    /**
     * Formatea las fechas para los encabezados.
     *
     * @return array
     */
    protected function formatDatesForHeadings()
    {
        return array_map(function ($date) {
            return Carbon::parse($date)->format('d/m/Y');
        }, $this->dates);
    }

    /**
     * Registra los eventos para aplicar estilos y auto-ajustes.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe
                $sheet = $event->sheet->getDelegate();

                // Desplazar contenido 6 filas hacia abajo
                $sheet->insertNewRowBefore(1, 6);

                // a. Título centrado y combinado
                $titleRange = "A1:" . $this->getColumnLetter(3 + count($this->dates) + 1) . "1";
                $sheet->mergeCells($titleRange);
                $sheet->setCellValue("A1", "REPORTE DE PRE-INSCRIPCION POR FACULTAD | ESCUELA DE POSGRADO - ADMISION $periodo");
                $sheet->getStyle("A1")->applyFromArray([
                    'font' => [
                        'size' => 25,
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // b. Fecha y hora en A2 y B2
                $sheet->setCellValue("A2", "Fecha y hora:");
                $sheet->setCellValue("B2", Carbon::now()->format('d/m/Y - H:i:s'));

                // c. Datos adicionales en A4, B4, A5, B5
                $sheet->setCellValue("A4", "Rector:");
                $sheet->setCellValue("B4", "Dr. Enrique Wilfredo Carpena Velásquez");
                $sheet->setCellValue("A5", "Director de Escuela:");
                $sheet->setCellValue("B5", "Dr. Leandro Agapito Aznarán Castillo");

                // Aplicar negrita a las etiquetas
                $sheet->getStyle("A2:A5")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                $sheet->getStyle("B2:B5")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Ajustar contenido de la tabla desde la fila 7
                $rowCount = 7; // Comenzamos después de las filas agregadas
                $facultads = Facultad::where('estado', true)->orderBy('nombre')->get();
                $columnCount = 3 + count($this->dates) + 1;

                foreach ($facultads as $facultad) {
                    // a. Aplicar estilo al encabezado de cada facultad
                    $headerRange = "A{$rowCount}:" . $this->getColumnLetter($columnCount) . "{$rowCount}";

                    $sheet->getStyle("A{$rowCount}:C{$rowCount}") // Primeras tres columnas
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFCE4D6');

                    $sheet->getStyle("D{$rowCount}:" . $this->getColumnLetter($columnCount) . "{$rowCount}") // Fechas + Acumulado
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFDDEBF7');

                    // Aplicar bordes al encabezado
                    $sheet->getStyle($headerRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                    // Negrita para todo el encabezado
                    $sheet->getStyle($headerRange)
                        ->getFont()->setBold(true);

                    // Incrementar fila después del encabezado
                    $rowCount++;

                    // b. Aplicar estilos y bordes al contenido de los programas
                    foreach ($this->sortedGrados as $gradoNombre) {
                        $grado = Grado::where('nombre', $gradoNombre)->first();
                        if (!$grado) continue;

                        $programas = Programa::where('facultad_id', $facultad->id)
                            ->where('grado_id', $grado->id)
                            ->where('estado', true)
                            ->orderBy('nombre')
                            ->get();

                        foreach ($programas as $programa) {
                            $rowRange = "A{$rowCount}:" . $this->getColumnLetter($columnCount) . "{$rowCount}";

                            // Aplicar bordes a la fila del contenido
                            $sheet->getStyle($rowRange)->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);

                            $rowCount++;
                        }
                    }

                    // c. Aplicar estilo a la fila de TOTAL
                    $totalRow = $rowCount;
                    $totalRange = "A{$totalRow}:" . $this->getColumnLetter($columnCount) . "{$totalRow}";

                    // Bordes para la fila de TOTAL
                    $sheet->getStyle($totalRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                    // Negrita para "TOTAL" y "ACUMULADO"
                    $sheet->getStyle("C{$totalRow}")
                        ->getFont()->setBold(true);
                    $sheet->getStyle($this->getColumnLetter($columnCount) . "{$totalRow}")
                        ->getFont()->setBold(true);

                    // Incrementar fila para la fila TOTAL
                    $rowCount++;

                    // d. Añadir una fila en blanco entre tablas
                    $rowCount++;
                }
            },
        ];
    }

    /**
     * Convierte un número de columna a su letra correspondiente (ej. 1 -> A).
     *
     * @param int $colNumber
     * @return string
     */
    protected function getColumnLetter($colNumber)
    {
        $letter = '';
        while ($colNumber > 0) {
            $temp = ($colNumber - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $colNumber = intval(($colNumber - $temp) / 26);
        }
        return $letter;
    }

    public function styles(Worksheet $sheet)
    {
        // Ejemplo: Aplicar negrita a todas las filas de encabezado
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'GRADOS ACADÉMICOS') {
                $sheet->getStyle('A' . $row . ':' . 'Z' . $row)->getFont()->setBold(true);
            }
        }
    }
}
