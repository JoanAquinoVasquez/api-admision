<?php

namespace App\Exports;

use App\Models\Programa;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InscripcionResumenSummarySheet implements WithTitle, WithStyles
{
    public function title(): string
    {
        return 'Hoja1';
    }

    public function styles(Worksheet $sheet)
    {
        // Habilitar líneas de cuadrícula visibles
        $sheet->setShowGridLines(true);

        // Ajustar anchos de columnas
        $sheet->getColumnDimension('A')->setWidth(91.85546875);
        $sheet->getColumnDimension('B')->setWidth(22.28515625);

        // Estilos de celda
        $headerStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E5E5'], // Gris suave
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ];

        $groupHeaderStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'], // Amarillo
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ];

        $programStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => false,
                'color' => ['rgb' => '000000'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ];

        $totalStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'], // Amarillo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '7F7F7F'],
                ],
            ],
        ];

        // Escribir cabecera en fila 3
        $sheet->setCellValue('A3', 'PROGRAMAS');
        $sheet->setCellValue('B3', 'Número de Postulantes');
        $sheet->getStyle('A3')->applyFromArray($headerStyle);
        $sheet->getStyle('B3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // Obtener programas con sus grados e inscripciones
        $programas = Programa::with(['grado', 'inscripciones'])->get();

        $maestrias = [];
        $doctorados = [];
        $segundas = [];

        foreach ($programas as $p) {
            $grado = $p->grado->nombre;
            $name = $p->nombre;
            $count = $p->inscripciones->count(); // Todas las inscripciones en la BD (incluyendo inactivas)

            $item = ['name' => $name, 'count' => $count];
            if ($grado === 'MAESTRIA') {
                $maestrias[] = $item;
            } elseif ($grado === 'DOCTORADO') {
                $doctorados[] = $item;
            } elseif ($grado === 'SEGUNDA ESPECIALIDAD PROFESIONAL') {
                $segundas[] = $item;
            }
        }

        // Normalización para ordenación alfabética estable
        $normalize = function($s) {
            if (!$s) return "";
            $s = trim($s);
            $s = mb_strtolower($s, 'UTF-8');
            
            $unwanted_array = [
                'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
                'à'=>'a', 'è'=>'e', 'ì'=>'i', 'ò'=>'o', 'ù'=>'u',
                'ä'=>'a', 'ë'=>'e', 'ï'=>'i', 'ö'=>'o', 'ü'=>'u',
                'â'=>'a', 'ê'=>'e', 'î'=>'i', 'ô'=>'o', 'û'=>'u',
                'ñ'=>'n', 'ç'=>'c',
                'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
                'À'=>'a', 'È'=>'e', 'Ì'=>'i', 'Ò'=>'o', 'Ù'=>'u',
                'Ä'=>'a', 'Ë'=>'e', 'Ï'=>'i', 'Ö'=>'o', 'Ü'=>'u',
                'Â'=>'a', 'Ê'=>'e', 'Î'=>'i', 'Ô'=>'o', 'Û'=>'u',
                'Ñ'=>'n', 'Ç'=>'c'
            ];
            
            return strtr($s, $unwanted_array);
        };

        // Ordenar cada grupo:
        // 1. count > 0 primero, ordenado por count desc y luego alfabéticamente
        // 2. count == 0 al último, ordenado alfabéticamente
        $sortGroup = function($group) use ($normalize) {
            $hasApp = [];
            $noApp = [];
            foreach ($group as $item) {
                if ($item['count'] > 0) {
                    $hasApp[] = $item;
                } else {
                    $noApp[] = $item;
                }
            }

            usort($hasApp, function($a, $b) use ($normalize) {
                if ($a['count'] !== $b['count']) {
                    return $b['count'] - $a['count'];
                }
                return strcmp($normalize($a['name']), $normalize($b['name']));
            });

            usort($noApp, function($a, $b) use ($normalize) {
                return strcmp($normalize($a['name']), $normalize($b['name']));
            });

            return array_merge($hasApp, $noApp);
        };

        $maestriasSorted = $sortGroup($maestrias);
        $doctoradosSorted = $sortGroup($doctorados);
        $segundasSorted = $sortGroup($segundas);

        $currentRow = 4;

        $writeGroup = function($degreeName, $items) use (&$sheet, &$currentRow, $groupHeaderStyle, $programStyle) {
            $headerRow = $currentRow;

            // Group Header
            $sheet->setCellValue("A{$headerRow}", $degreeName);
            $sheet->getStyle("A{$headerRow}")->applyFromArray($groupHeaderStyle);
            $sheet->getStyle("A{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $currentRow++;
            $startRow = $currentRow;

            foreach ($items as $item) {
                $sheet->setCellValue("A{$currentRow}", $item['name']);
                $sheet->setCellValue("B{$currentRow}", $item['count']);

                $sheet->getStyle("A{$currentRow}")->applyFromArray($programStyle);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("B{$currentRow}")->applyFromArray($programStyle);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($currentRow)->setRowHeight(20);

                $currentRow++;
            }

            $endRow = $currentRow - 1;

            // Escribir fórmula de suma en la cabecera
            $sheet->setCellValue("B{$headerRow}", "=SUM(B{$startRow}:B{$endRow})");
            $sheet->getStyle("B{$headerRow}")->applyFromArray($groupHeaderStyle);
            $sheet->getStyle("B{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($headerRow)->setRowHeight(20);

            return $headerRow;
        };

        // Escribir Maestrias
        $maeHeaderRow = $writeGroup('MAESTRIA', $maestriasSorted);

        // Escribir Doctorados
        $docHeaderRow = $writeGroup('DOCTORADO', $doctoradosSorted);

        // Escribir Segundas Especialidades
        $seHeaderRow = $writeGroup('SEGUNDA ESPECIALIDAD PROFESIONAL', $segundasSorted);

        // Escribir Fila de Total
        $sheet->setCellValue("A{$currentRow}", 'TOTAL ');
        $sheet->setCellValue("B{$currentRow}", "=B{$maeHeaderRow}+B{$docHeaderRow}+B{$seHeaderRow}");
        
        $sheet->getStyle("A{$currentRow}")->applyFromArray($totalStyle);
        $sheet->getStyle("B{$currentRow}")->applyFromArray($totalStyle);
        $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($currentRow)->setRowHeight(20);
    }
}
