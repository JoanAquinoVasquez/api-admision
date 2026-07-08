<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class CargoConstanciasProgramSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $programa;
    protected $inscripciones;

    public function __construct($programa, $inscripciones)
    {
        $this->programa = $programa;
        $this->inscripciones = $inscripciones;
    }

    public function title(): string
    {
        // Quitar caracteres no permitidos en pestañas de Excel
        $name = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $this->programa->nombre);
        // Abreviar nombres para evitar exceder el límite de 31 caracteres
        $name = str_replace(
            [
                'MAESTRÍA EN CIENCIAS CON MENCIÓN EN',
                'MAESTRÍA EN',
                'DOCTORADO EN',
                'SEGUNDA ESPECIALIDAD EN',
                'SEGUNDA ESPECIALIDAD PROFESIONAL EN',
                'CON MENCIÓN EN',
                'con mención en',
                'con Mención en'
            ],
            [
                'MC',
                'MSTR',
                'DOCT',
                'SEG ESP',
                'SEG ESP',
                '',
                '',
                ''
            ],
            $name
        );
        
        $name = mb_strtoupper(trim($name));
        return mb_substr($name, 0, 30);
    }

    public function collection()
    {
        $data = new Collection();
        $contador = 1;

        foreach ($this->inscripciones as $inscripcion) {
            $postulante = $inscripcion->postulante;
            $nombreCompleto = mb_strtoupper($postulante->ap_paterno . ' ' . $postulante->ap_materno . ' ' . $postulante->nombres);
            
            $data->push([
                'EMPTY' => '',
                'NRO' => $contador++,
                'APELLIDOS Y NOMBRES' => $nombreCompleto,
                'FIRMA' => '',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            ['', 'NRO', 'APELLIDOS Y NOMBRES', 'FIRMA'],
            ['', '', '', '']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(3.44);
        $sheet->getColumnDimension('B')->setWidth(5.0);
        $sheet->getColumnDimension('C')->setWidth(47.55);
        $sheet->getColumnDimension('D')->setWidth(25.0); // Ampliado para el espacio de firma

        // 2. Insertar filas en blanco al inicio para título y metadatos
        $sheet->insertNewRowBefore(1, 13);

        // 3. Formato del título y cabecera
        // B4: UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO
        $sheet->mergeCells('B4:D4');
        $sheet->setCellValue('B4', 'UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // B5: ESCUELA DE POSGRADO
        $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('B5', 'ESCUELA DE POSGRADO');
        $sheet->getStyle('B5')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // B7: PROGRAMA (Fila de altura variable y con ajuste de texto)
        $sheet->getRowDimension(7)->setRowHeight(27.6);
        $sheet->mergeCells('B7:D7');
        
        $gradoStr = mb_strtoupper($this->programa->grado->nombre);
        $progStr = mb_strtoupper($this->programa->nombre);

        if (str_starts_with($progStr, $gradoStr) || str_starts_with($progStr, 'MAESTRÍA') || str_starts_with($progStr, 'DOCTORADO')) {
            $titulo = $progStr;
        } else {
            $titulo = $gradoStr . ' EN ' . $progStr;
        }
        $titulo .= ' PROM. ______'; // Espacio en blanco para la promoción

        $sheet->setCellValue('B7', $titulo);
        $sheet->getStyle('B7')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // B8: ADMISIÓN período
        $sheet->mergeCells('B8:D8');
        $periodo = config('admission.cronograma.periodo');
        $sheet->setCellValue('B8', 'ADMISIÓN ' . $periodo);
        $sheet->getStyle('B8')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // C10: SUBTÍTULO
        $sheet->mergeCells('C10:D10');
        $sheet->setCellValue('C10', 'ENTREGA DE CONSTANCIAS DE INGRESO');
        $sheet->getStyle('C10')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // B12: COORDINADOR:
        $sheet->mergeCells('B12:D12');
        $sheet->setCellValue('B12', 'COORDINADOR:');
        $sheet->getStyle('B12')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 4. Combinar las cabeceras de la tabla (filas 14 y 15)
        $sheet->mergeCells('B14:B15');
        $sheet->mergeCells('C14:C15');
        $sheet->mergeCells('D14:D15');

        // Estilos para las cabeceras de la tabla
        $sheet->getStyle('B14:D15')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $highestRow = $sheet->getHighestRow();

        // 5. Aplicar bordes de la tabla y fuente Calibri a los datos
        $sheet->getStyle("B16:D{$highestRow}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Alinear NRO al centro y APELLIDOS Y NOMBRES a la izquierda y darles altura 24
        for ($r = 16; $r <= $highestRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(24.0);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // 6. Bloque de firma
        $firmaRow = $highestRow + 5;
        $sheet->getStyle("D{$firmaRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->setCellValue("D{$firmaRow}", 'Firma del Docente');
    }
}
