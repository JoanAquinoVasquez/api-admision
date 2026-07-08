<?php

namespace App\Exports;

use App\Models\Programa;
use App\Models\Inscripcion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class EstadisticasAdmisionExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        // Obtener programas activos ordenados por Grado y luego alfabéticamente
        $programas = Programa::with(['grado'])
            ->where('estado', 1)
            ->get()
            ->sortBy(function ($p) {
                return $p->grado_id . '_' . $p->nombre;
            })
            ->values();

        $reportData = new Collection();
        $contador = 2; // Fila de inicio de los datos de 2026-I en Excel antes de insertar cabeceras (se desplazará 5 filas abajo)

        foreach ($programas as $p) {
            // Inscripciones activas para este programa
            $inscripciones = Inscripcion::with(['postulante', 'nota'])
                ->where('programa_id', $p->id)
                ->where('inscripcions.estado', 1)
                ->get();

            // Conteo de postulantes por sexo
            $postulantesH = $inscripciones->filter(function ($ins) {
                return $ins->postulante && $ins->postulante->sexo === 'M';
            })->count();

            $postulantesM = $inscripciones->filter(function ($ins) {
                return $ins->postulante && $ins->postulante->sexo === 'F';
            })->count();

            // Conteo de ingresantes por sexo (deben tener las tres notas numéricas)
            $ingresantes = $inscripciones->filter(function ($ins) {
                $nota = $ins->nota;
                return $nota &&
                    is_numeric($nota->cv) &&
                    is_numeric($nota->entrevista) &&
                    is_numeric($nota->examen);
            });

            $ingresantesH = $ingresantes->filter(function ($ins) {
                return $ins->postulante && $ins->postulante->sexo === 'M';
            })->count();

            $ingresantesM = $ingresantes->filter(function ($ins) {
                return $ins->postulante && $ins->postulante->sexo === 'F';
            })->count();

            // Formatear nombre del programa
            $gradoStr = mb_strtoupper($p->grado->nombre);
            $progStr = mb_strtoupper($p->nombre);

            if (str_starts_with($progStr, $gradoStr) || str_starts_with($progStr, 'MAESTRÍA') || str_starts_with($progStr, 'MAESTRIA') || str_starts_with($progStr, 'DOCTORADO')) {
                $name = $progStr;
            } else {
                if ($p->grado_id == 1) {
                    $name = 'DOCTORADO EN ' . $progStr;
                } elseif ($p->grado_id == 2) {
                    $name = 'MAESTRIA EN ' . $progStr;
                } elseif ($p->grado_id == 3) {
                    $name = 'SEGUNDA ESPECIALIDAD EN ' . $progStr;
                } else {
                    $name = $gradoStr . ' EN ' . $progStr;
                }
            }

            $reportData->push([
                'programa' => $name,
                'post_h' => $postulantesH,
                'post_m' => $postulantesM,
                'post_t' => "=SUM(B{$contador}:C{$contador})",
                'ing_h' => $ingresantesH,
                'ing_m' => $ingresantesM,
                'ing_t' => "=SUM(E{$contador}:F{$contador})",
            ]);

            $contador++;
        }

        return $reportData;
    }

    public function headings(): array
    {
        return [
            ['Programa', 'Hombres', 'Mujeres', 'Total ', 'Hombres', 'Mujeres', 'Total ']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Configurar anchos de columna como en la plantilla
        $sheet->getColumnDimension('A')->setWidth(85.14);
        $sheet->getColumnDimension('B')->setWidth(11.71);
        $sheet->getColumnDimension('C')->setWidth(12.14);
        $sheet->getColumnDimension('D')->setWidth(9.85);
        $sheet->getColumnDimension('E')->setWidth(13.0);
        $sheet->getColumnDimension('F')->setWidth(10.57);
        $sheet->getColumnDimension('G')->setWidth(9.14);
        $sheet->getColumnDimension('H')->setWidth(13.0);
        $sheet->getColumnDimension('I')->setWidth(13.0);
        $sheet->getColumnDimension('J')->setWidth(13.0);
        $sheet->getColumnDimension('K')->setWidth(30.71);
        $sheet->getColumnDimension('L')->setWidth(13.0);

        // 2. Insertar 5 filas arriba para colocar título general y título de 2026-I
        $sheet->insertNewRowBefore(1, 5);

        // A2: Título del Reporte
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'REPORTE ESTADISTICO DE ADMISIÓN  DEL POSGRADO DE LOS ULTIMOS TRES PROCESOS');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 18,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Estilos reutilizables
        $yellowFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF00'],
        ];

        $headerFont = [
            'name' => 'Calibri',
            'size' => 14,
            'bold' => true,
        ];

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // ==========================================
        // SECCIÓN 1: PROCESO 2026-I (Dinámico)
        // ==========================================
        $sheet->setCellValue('A4', 'PROCESO DE ADMISIÓN 2026-I');
        $sheet->getStyle('A4')->getFont()->setName('Calibri')->setSize(16)->setBold(true);

        // Cabeceras de 2026-I
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', 'Programa');

        $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('B5', 'Postulantes');

        $sheet->mergeCells('E5:G5');
        $sheet->setCellValue('E5', 'Ingresantes');

        $sheet->getStyle('A5:G6')->applyFromArray([
            'font' => $headerFont,
            'fill' => $yellowFill,
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A5:G6')->applyFromArray($thinBorder);

        $highestRow2026 = $sheet->getHighestRow();

        // Estilar datos de 2026-I (filas 7 a 22)
        $sheet->getStyle("A7:G{$highestRow2026}")->applyFromArray($thinBorder);
        for ($r = 7; $r <= $highestRow2026; $r++) {
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$r}:G{$r}")->getFont()->setName('Calibri')->setSize(12);
        }

        // Fila Total de 2026-I
        $totalRow2026 = $highestRow2026 + 1;
        $sheet->setCellValue("A{$totalRow2026}", 'TOTAL');
        $sheet->setCellValue("B{$totalRow2026}", "=SUM(B7:B{$highestRow2026})");
        $sheet->setCellValue("C{$totalRow2026}", "=SUM(C7:C{$highestRow2026})");
        $sheet->setCellValue("D{$totalRow2026}", "=SUM(D7:D{$highestRow2026})");
        $sheet->setCellValue("E{$totalRow2026}", "=SUM(E7:E{$highestRow2026})");
        $sheet->setCellValue("F{$totalRow2026}", "=SUM(F7:F{$highestRow2026})");
        $sheet->setCellValue("G{$totalRow2026}", "=SUM(G7:G{$highestRow2026})");

        $sheet->getStyle("A{$totalRow2026}:G{$totalRow2026}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle("A{$totalRow2026}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$totalRow2026}")->getFill()->applyFromArray($yellowFill);
        $sheet->getStyle("B{$totalRow2026}:G{$totalRow2026}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ==========================================
        // SECCIÓN 2: PROCESO 2025-I (Estático)
        // ==========================================
        $sec2Start = $totalRow2026 + 3;
        $sheet->setCellValue("A{$sec2Start}", 'PROCESO DE ADMISIÓN 2025-I');
        $sheet->getStyle("A{$sec2Start}")->getFont()->setName('Calibri')->setSize(16)->setBold(true);

        $h1Row = $sec2Start + 1; // Cabecera nivel 1
        $h2Row = $sec2Start + 2; // Cabecera nivel 2

        $sheet->mergeCells("A{$h1Row}:A{$h2Row}");
        $sheet->setCellValue("A{$h1Row}", 'Programa');

        $sheet->mergeCells("B{$h1Row}:D{$h1Row}");
        $sheet->setCellValue("B{$h1Row}", 'Postulantes');

        $sheet->mergeCells("E{$h1Row}:G{$h1Row}");
        $sheet->setCellValue("E{$h1Row}", 'Ingresantes');

        $sheet->setCellValue("B{$h2Row}", 'Hombres');
        $sheet->setCellValue("C{$h2Row}", 'Mujeres');
        $sheet->setCellValue("D{$h2Row}", 'Total ');
        $sheet->setCellValue("E{$h2Row}", 'Hombres');
        $sheet->setCellValue("F{$h2Row}", 'Mujeres');
        $sheet->setCellValue("G{$h2Row}", 'Total ');

        $sheet->getStyle("A{$h1Row}:G{$h2Row}")->applyFromArray([
            'font' => $headerFont,
            'fill' => $yellowFill,
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle("A{$h1Row}:G{$h2Row}")->applyFromArray($thinBorder);

        $data2025 = [
            ['DOCTORADO EN CIENCIAS DE LA EDUCACIÓN', 12, 10, 12, 10],
            ['DOCTORADO EN DERECHO Y CIENCIA POLÍTICA', 4, 17, 4, 16],
            ['MAESTRIA EN ADMINISTRACIÓN CON MENCIÓN EN GERENCIA EMPRESARIAL', 15, 8, 13, 8],
            ['MAESTRIA EN CIENCIAS CON MENCIÓN EN PROYECTOS DE INVERSIÓN', 12, 9, 12, 7],
            ['MAESTRIA EN CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN DOCENCIA Y GESTIÓN UNIVERSITARIA', 15, 21, 13, 20],
            ['MAESTRIA EN CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN INVESTIGACIÓN Y DOCENCIA', 12, 12, 12, 12],
            ['MAESTRIA EN CIENCIAS DE LA INGENIERÍA MECÁNICA Y ELÉCTRICA CON MENCIÓN EN ENERGÍA', 22, 0, 21, 0],
            ['MAESTRIA EN CIENCIAS SOCIALES CON MENCIÓN EN GESTIÓN PÚBLICA Y GERENCIA SOCIAL', 17, 20, 16, 19],
            ['MAESTRIA EN DERECHO CON MENCIÓN EN CIVIL Y COMERCIAL', 7, 14, 7, 13],
            ['MAESTRIA EN DERECHO CON MENCIÓN EN DERECHO PENAL Y PROCESAL PENAL', 13, 19, 13, 18],
            ['MAESTRIA EN GERENCIA DE OBRAS Y CONSTRUCCIÓN', 26, 10, 26, 10],
            ['MAESTRIA EN GESTIÓN INTEGRADA DE LOS RECURSOS HÍDRICOS', 22, 8, 19, 8],
            ['SEGUNDA ESPECIALIDAD EN ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN EMERGENCIA Y DESASTRES CON MENCIÓN EN CUIDADOS HOSPITALARIOS', 1, 29, 1, 26],
            ['SEGUNDA ESPECIALIDAD EN ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN CENTRO QUIRÚRGICO ESPECIALIZADO CON MENCIÓN EN CENTRO QUIRÚRGICO', 3, 41, 3, 39],
            ['SEGUNDA ESPECIALIDAD EN ÁREA DEL CUIDADO A LA PERSONA ESPECIALISTA EN ENFERMERÍA NEFROLÓGICA Y UROLÓGICA CON MENCIÓN EN DIÁLISIS', 1, 19, 1, 18],
            ['SEGUNDA ESPECIALIDAD EN GESTIÓN AMBIENTAL', 12, 11, 9, 10]
        ];

        $currRow = $h2Row + 1;
        $dataStart2025 = $currRow;
        foreach ($data2025 as $row) {
            $sheet->setCellValue("A{$currRow}", $row[0]);
            $sheet->setCellValue("B{$currRow}", $row[1]);
            $sheet->setCellValue("C{$currRow}", $row[2]);
            $sheet->setCellValue("D{$currRow}", "=SUM(B{$currRow}:C{$currRow})");
            $sheet->setCellValue("E{$currRow}", $row[3]);
            $sheet->setCellValue("F{$currRow}", $row[4]);
            $sheet->setCellValue("G{$currRow}", "=SUM(E{$currRow}:F{$currRow})");

            $sheet->getStyle("A{$currRow}:G{$currRow}")->applyFromArray($thinBorder);
            $sheet->getStyle("A{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$currRow}:G{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currRow}:G{$currRow}")->getFont()->setName('Calibri')->setSize(12);

            $currRow++;
        }
        $dataEnd2025 = $currRow - 1;

        // Fila Total de 2025-I
        $totalRow2025 = $currRow;
        $sheet->setCellValue("A{$totalRow2025}", 'TOTAL');
        $sheet->setCellValue("B{$totalRow2025}", "=SUM(B{$dataStart2025}:B{$dataEnd2025})");
        $sheet->setCellValue("C{$totalRow2025}", "=SUM(C{$dataStart2025}:C{$dataEnd2025})");
        $sheet->setCellValue("D{$totalRow2025}", "=SUM(D{$dataStart2025}:D{$dataEnd2025})");
        $sheet->setCellValue("E{$totalRow2025}", "=SUM(E{$dataStart2025}:E{$dataEnd2025})");
        $sheet->setCellValue("F{$totalRow2025}", "=SUM(F{$dataStart2025}:F{$dataEnd2025})");
        $sheet->setCellValue("G{$totalRow2025}", "=SUM(G{$dataStart2025}:G{$dataEnd2025})");

        $sheet->getStyle("A{$totalRow2025}:G{$totalRow2025}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle("A{$totalRow2025}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$totalRow2025}")->getFill()->applyFromArray($yellowFill);
        $sheet->getStyle("B{$totalRow2025}:G{$totalRow2025}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // ==========================================
        // SECCIÓN 3: PROCESO 2024-II (Estático)
        // ==========================================
        $sec3Start = $totalRow2025 + 3;
        $sheet->setCellValue("A{$sec3Start}", 'PROCESO DE ADMISIÓN 2024-II');
        $sheet->getStyle("A{$sec3Start}")->getFont()->setName('Calibri')->setSize(16)->setBold(true);

        $h1Row3 = $sec3Start + 1; // Cabecera nivel 1
        $h2Row3 = $sec3Start + 2; // Cabecera nivel 2

        $sheet->mergeCells("A{$h1Row3}:A{$h2Row3}");
        $sheet->setCellValue("A{$h1Row3}", 'Programa');

        $sheet->mergeCells("B{$h1Row3}:D{$h1Row3}");
        $sheet->setCellValue("B{$h1Row3}", 'Postulantes');

        $sheet->mergeCells("E{$h1Row3}:G{$h1Row3}");
        $sheet->setCellValue("E{$h1Row3}", 'Ingresantes');

        $sheet->setCellValue("B{$h2Row3}", 'Hombres');
        $sheet->setCellValue("C{$h2Row3}", 'Mujeres');
        $sheet->setCellValue("D{$h2Row3}", 'Total ');
        $sheet->setCellValue("E{$h2Row3}", 'Hombres');
        $sheet->setCellValue("F{$h2Row3}", 'Mujeres');
        $sheet->setCellValue("G{$h2Row3}", 'Total ');

        $sheet->getStyle("A{$h1Row3}:G{$h2Row3}")->applyFromArray([
            'font' => $headerFont,
            'fill' => $yellowFill,
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle("A{$h1Row3}:G{$h2Row3}")->applyFromArray($thinBorder);

        $data2024 = [
            ['DOCTORADO EN ADMINISTRACIÓN', 16, 12, 14, 11],
            ['DOCTORADO EN CIENCIAS AMBIENTALES', 19, 7, 19, 7],
            ['DOCTORADO EN CIENCIAS DE LA EDUCACIÓN', 27, 15, 27, 14],
            ['DOCTORADO EN DERECHO Y CIENCIA POLÍTICA', 16, 7, 14, 8],
            ['MAESTRÍA EN CIENCIAS CON MENCIÓN EN GESTIÓN DE LA CALIDAD E INOCUIDAD DE ALIMENTOS', 9, 16, 8, 15],
            ['MAESTRÍA EN DERECHO CON MENCIÓN EN DERECHO CONSTITUCIONAL Y PROCESAL CONSTITUCIONAL', 10, 12, 10, 12],
            ['MAESTRÍA EN DERECHO CON MENCIÓN EN DERECHO PENAL Y PROCESAL PENAL', 30, 23, 29, 19],
            ['MAESTRÍA EN CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN DOCENCIA Y GESTIÓN UNIVERSITARIA', 16, 21, 13, 22],
            ['MAESTRÍA EN CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN GERENCIA EDUCATIVA ESTRATÉGICA', 4, 15, 4, 14],
            ['MAESTRÍA EN INGENIERÍA DE SISTEMAS CON MENCIÓN EN GERENCIA DE TECNOLOGÍAS DE LA INFORMACIÓN Y GESTIÓ DEL SOFTWARE', 24, 3, 23, 3],
            ['MAESTRÍA EN CIENCIAS SOCIALES CON MENCIÓN EN GESTIÓN PÚBLICA Y GERENCIA SOCIAL', 24, 16, 21, 15],
            ['MAESTRÍA EN CIENCIAS DE LA EDUCACIÓN CON MENCIÓN EN INVESTIGACIÓN Y DOCENCIA', 12, 15, 11, 14],
            ['MAESTRÍA EN GERENCIA DE OBRAS Y CONSTRUCCIÓN', 53, 7, 52, 5],
            ['MAESTRÍA EN CIENCIAS CON MENCIÓN EN ORDENAMIENTO TERRITORIAL Y DESARROLLO URBANO', 14, 18, 13, 15],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN EMERGENCIA Y DESASTRES CON MENCIÓN EN CUIDADOS HOSPITALARIOS', 4, 39, 4, 35],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN CENTRO QUIRÚRGICO ESPECIALIZADO CON MENCIÓN EN CENTRO QUIRÚRGICO', 1, 29, 1, 28],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ESPECIALISTA EN ENFERMERÍA NEFROLÓGICA Y UROLÓGICA CON MENCIÓN EN DIÁLISIS', 1, 10, 1, 9],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN CUIDADOS CRÍTICOS CON MENCIÓN EN NEONATOLOGÍA', 0, 18, 0, 18],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ESPECIALISTA EN ENFERMERÍA PEDIÁTRICA Y NEONATOLOGÍA CON MENCIÓN EN PEDIATRÍA', 0, 16, 0, 16],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN CUIDADOS CRÍTICOS CON MENCIÓN EN ADULTO', 3, 14, 3, 14],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ENFERMERA ESPECIALISTA EN GASTROENTEROLOGÍA Y PROCEDIMIENTOS ENDOSCÁPICOS CON MENCIÓN EN PROCEDIMIENTOS ENDOSCÁPICOS', 1, 14, 1, 14],
            ['S.E. - ÁREA DEL CUIDADO A LA PERSONA ESPECIALISTA EN ENFERMERÍA ONCOLÓGICA CON MENCIÓN EN ONCOLOGÍA', 1, 14, 1, 13]
        ];

        $currRow = $h2Row3 + 1;
        $dataStart2024 = $currRow;
        foreach ($data2024 as $row) {
            $sheet->setCellValue("A{$currRow}", $row[0]);
            $sheet->setCellValue("B{$currRow}", $row[1]);
            $sheet->setCellValue("C{$currRow}", $row[2]);
            $sheet->setCellValue("D{$currRow}", "=SUM(B{$currRow}:C{$currRow})");
            $sheet->setCellValue("E{$currRow}", $row[3]);
            $sheet->setCellValue("F{$currRow}", $row[4]);
            $sheet->setCellValue("G{$currRow}", "=SUM(E{$currRow}:F{$currRow})");

            $sheet->getStyle("A{$currRow}:G{$currRow}")->applyFromArray($thinBorder);
            $sheet->getStyle("A{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$currRow}:G{$currRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currRow}:G{$currRow}")->getFont()->setName('Calibri')->setSize(12);

            $currRow++;
        }
        $dataEnd2024 = $currRow - 1;

        // Fila Total de 2024-II
        $totalRow2024 = $currRow;
        $sheet->setCellValue("A{$totalRow2024}", 'TOTAL');
        $sheet->setCellValue("B{$totalRow2024}", "=SUM(B{$dataStart2024}:B{$dataEnd2024})");
        $sheet->setCellValue("C{$totalRow2024}", "=SUM(C{$dataStart2024}:C{$dataEnd2024})");
        $sheet->setCellValue("D{$totalRow2024}", "=SUM(D{$dataStart2024}:D{$dataEnd2024})");
        $sheet->setCellValue("E{$totalRow2024}", "=SUM(E{$dataStart2024}:E{$dataEnd2024})");
        $sheet->setCellValue("F{$totalRow2024}", "=SUM(F{$dataStart2024}:F{$dataEnd2024})");
        $sheet->setCellValue("G{$totalRow2024}", "=SUM(G{$dataStart2024}:G{$dataEnd2024})");

        $sheet->getStyle("A{$totalRow2024}:G{$totalRow2024}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle("A{$totalRow2024}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$totalRow2024}")->getFill()->applyFromArray($yellowFill);
        $sheet->getStyle("B{$totalRow2024}:G{$totalRow2024}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // ==========================================
        // BLOQUE DE TOTAL GENERAL (Columnas J y K)
        // Colocados al costado de la primera tabla
        // ==========================================
        $sheet->setCellValue("J{$totalRow2026}", "=G{$totalRow2026}+G{$totalRow2025}+G{$totalRow2024}");
        $sheet->setCellValue("K{$totalRow2026}", "INGRESANTES DE 24-II 25-I 26-I");

        $sheet->getStyle("J{$totalRow2026}:K{$totalRow2026}")->applyFromArray($thinBorder);
        $sheet->getStyle("J{$totalRow2026}")->getFont()->setName('Calibri')->setSize(11)->setBold(false);
        $sheet->getStyle("J{$totalRow2026}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K{$totalRow2026}")->getFont()->setName('Calibri')->setSize(11)->setBold(false);
        $sheet->getStyle("K{$totalRow2026}")->getFill()->applyFromArray($yellowFill);
        $sheet->getStyle("K{$totalRow2026}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
