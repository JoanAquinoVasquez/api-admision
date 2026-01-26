<?php

namespace App\Exports;

use App\Models\Inscripcion;
use App\Models\Programa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InscripcionDiarioExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $dates;

    public function __construct()
    {
        // Obtener todas las fechas únicas de preinscripciones ordenadas
        $this->dates = Inscripcion::selectRaw('DATE(created_at) as date')
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();
    }

    /**
     * Recopila todos los datos necesarios para el reporte.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener todos los programas con relaciones y ordenados por el nombre del grado
        $programas = Programa::with(['grado', 'facultad'])
            ->join('grados', 'programas.grado_id', '=', 'grados.id')
            ->orderByRaw("
            CASE 
                WHEN grados.nombre = 'MAESTRÍA' THEN 1
                WHEN grados.nombre = 'DOCTORADO' THEN 2
                WHEN grados.nombre = 'SEGUNDA ESPECIALIDAD PROFESIONAL' THEN 3
                ELSE 4
            END
        ")
            ->select('programas.*') // Selecciona solo las columnas de "programas"
            ->get();

        // Obtener las inscripciones agrupadas por programa y fecha
        $preinscripciones = Inscripcion::selectRaw('programas.grado_id, inscripcions.programa_id, DATE(inscripcions.created_at) as date, COUNT(*) as count')
            ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
            ->join('grados', 'programas.grado_id', '=', 'grados.id')
            ->groupBy('programas.grado_id', 'inscripcions.programa_id', 'date')
            ->orderByRaw("
            CASE 
                WHEN grados.nombre = 'MAESTRÍA' THEN 1
                WHEN grados.nombre = 'DOCTORADO' THEN 2
                WHEN grados.nombre = 'SEGUNDA ESPECIALIDAD PROFESIONAL' THEN 3
                ELSE 4
            END
        ")
            ->get()
            ->groupBy('programa_id');

        // Construir la colección de datos para el reporte
        $reportData = [];
        $columnSums = array_fill_keys($this->dates, 0); // Inicializar suma por columna
        $totalAcumulado = 0; // Inicializar suma total del acumulado

        foreach ($programas as $programa) {
            $row = [
                'grado' => $programa->grado->nombre, // Ajusta según el nombre de la relación
                'facultad' => $programa->facultad->siglas,
                'programa' => $programa->nombre,
            ];

            $acumulado = 0;

            foreach ($this->dates as $date) {
                $count = 0;
                if (isset($preinscripciones[$programa->id])) {
                    $count = $preinscripciones[$programa->id]->firstWhere('date', $date)->count ?? 0;
                }
                $row[$date] = $count;
                $acumulado += $count;
                $columnSums[$date] += $count; // Sumar al total de la columna
            }

            $row['acumulado'] = $acumulado;
            $totalAcumulado += $acumulado; // Sumar al total acumulado

            $reportData[] = $row;
        }

        // Agregar fila total al final
        $totalRow = [
            'grado' => '',
            'facultad' => '',
            'programa' => 'ACUMULADO',
        ];

        foreach ($this->dates as $date) {
            $totalRow[$date] = $columnSums[$date];
        }

        $totalRow['acumulado'] = $totalAcumulado;

        $reportData[] = $totalRow;

        return collect($reportData);
    }


    /**
     * Define los encabezados del reporte.
     *
     * @return array
     */
    public function headings(): array
    {
        return array_merge(
            ['GRADOS ACADÉMICOS', 'FACULTAD', 'PROGRAMAS ACADÉMICOS'],
            $this->formatDatesForHeadings(),
            ['ACUMULADO'] // Encabezado para la suma por fila
        );
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
     * Mapea cada fila para el reporte.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $data = [
            $row['grado'],
            $row['facultad'],
            $row['programa'],
        ];

        $sum = 0; // Variable para almacenar la suma de la fila

        foreach ($this->dates as $date) {
            $value = $row[$date] > 0 ? $row[$date] : 0; // Asegurarse de que no haya valores vacíos
            $data[] = $value;
            $sum += $value; // Acumular los valores
        }

        $data[] = $sum; // Agregar la suma al final de la fila

        return $data;
    }

    /**
     * Ajusta el tamaño de las columnas y aplica estilos.
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe

        // Desplazar contenido hacia abajo para espacio adicional
        $sheet->insertNewRowBefore(1, 5); // Inserta 5 filas desde la primera fila

        // Primera fila: Título combinado y centrado
        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
        $sheet->setCellValue('A1', 'REPORTE DE INSCRIPCION | ESCUELA DE POSGRADO - ADMISIÓN ' . $periodo);
        $sheet->getStyle('A1')->getFont()->setSize(20)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tercera fila: Fecha
        $sheet->setCellValue('A3', 'Fecha:');
        $sheet->setCellValue('B3', Carbon::now()->format('d/m/Y'));
        $sheet->getStyle('A3')->getFont()->setBold(true);

        // Cuarta fila: Hora
        $sheet->setCellValue('A4', 'Hora:');
        $sheet->setCellValue('B4', Carbon::now()->format('H:i:s'));
        $sheet->getStyle('A4')->getFont()->setBold(true);

        // Ajustar encabezados (negrita y centrados)
        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')->getFont()->setBold(true);
        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setAutoFilter('A6:' . $sheet->getHighestColumn() . '6');

        // Aplicar bordes "Todos los bordes" a todo el contenido excepto las dos primeras celdas de la fila acumulada
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Eliminar bordes de las dos primeras celdas de la fila acumulada
        $sheet->getStyle("A{$lastRow}:B{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                ],
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                ],
            ],
        ]);

        // Aplicar bordes "Borde exterior grueso" al contorno general
        $sheet->getStyle("A6:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ]);

        // Poner en negrita toda la fila y columna del acumulado
        $acumuladoColumn = $lastColumn; // Última columna corresponde al acumulado

        // Poner en negrita la columna del acumulado
        $sheet->getStyle("{$acumuladoColumn}6:{$acumuladoColumn}{$lastRow}")->getFont()->setBold(true);

        // Poner en negrita la fila del acumulado
        $sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")->getFont()->setBold(true);

        // Combinar y centrar grados repetidos
        $currentGrado = null;
        $startRow = 7; // Empieza después de las filas adicionales
        $endRow = 7;

        for ($row = 7; $row <= $lastRow; $row++) {
            $grado = $sheet->getCell("A{$row}")->getValue();

            if ($grado !== $currentGrado) {
                // Si cambia el grado, combina las celdas anteriores
                if ($currentGrado !== null) {
                    $sheet->mergeCells("A{$startRow}:A{$endRow}");
                    $sheet->getStyle("A{$startRow}:A{$endRow}")
                        ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }

                // Actualiza el grado actual y reinicia las filas
                $currentGrado = $grado;
                $startRow = $row;
            }

            // Actualiza la fila final
            $endRow = $row;
        }

        // Combina las últimas filas
        if ($currentGrado !== null) {
            $sheet->mergeCells("A{$startRow}:A{$endRow}");
            $sheet->getStyle("A{$startRow}:A{$endRow}")
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }
    }
}
