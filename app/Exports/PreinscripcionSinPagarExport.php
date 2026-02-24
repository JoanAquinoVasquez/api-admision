<?php

namespace App\Exports;

use App\Models\PreInscripcion;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class PreinscripcionSinPagarExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        $preInscripciones = PreInscripcion::whereNotIn('num_iden', function ($query) {
            $query->select('num_iden')
                ->from('vouchers');
        })->get();

        return $preInscripciones->map(function ($preinscripcion) {
            return [
                'ID' => $preinscripcion->id,
                'Nombres' => $preinscripcion->nombres,
                'Apellidos' => $preinscripcion->ap_paterno . ' ' . $preinscripcion->ap_materno,
                'Correo' => $preinscripcion->email,
                'Tipo Doc.' => $preinscripcion->tipo_doc,
                'N° ID' => $preinscripcion->num_iden,
                'Celular' => $preinscripcion->celular,
                'Fecha de Nacimiento' => $preinscripcion->fecha_nacimiento,
                'Sexo' => $preinscripcion->sexo == 'M' ? 'Masculino' : 'Femenino',
                'Departamento' => $preinscripcion->distrito->provincia->departamento->nombre ?? 'N/A',
                'Provincia' => $preinscripcion->distrito->provincia->nombre ?? 'N/A',
                'Distrito' => $preinscripcion->distrito->nombre ?? 'N/A',
                'Facultad' => $preinscripcion->programa->facultad->nombre ?? 'N/A',
                'Grado' => $preinscripcion->programa->grado->nombre ?? 'N/A',
                'Programa' => $preinscripcion->programa->nombre ?? 'N/A',
                'Universidad Procedencia' => $preinscripcion->uni_procedencia ?? 'N/A',
                'Centro de Trabajo' => $preinscripcion->centro_trabajo ?? 'N/A',
                'Cargo' => $preinscripcion->cargo ?? 'N/A',
                'Fecha de Pre-Inscripción' => $preinscripcion->created_at,
            ];
        });
    }

    public function headings(): array
    {
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE PERSONAS PRE-INSCRITAS QUE AUN NO PAGAN SU INSCRIPCION - " . $timeActual; // Encabezado especial
        return [
            [$programaHeading], // Primera fila con encabezado especial
            [ // Segunda fila con encabezados estándar
                'ID',
                'Nombres',
                'Apellidos',
                'Correo',
                'T. Doc Identidad',
                'N. Identidad',
                'Celular',
                'Fecha de Nacimiento',
                'Sexo',
                'Departamento',
                'Provincia',
                'Distrito',
                'Facultad',
                'Grado',
                'Programa',
                'Universidad Procedencia',
                'Centro de Trabajo',
                'Cargo',
                'Fecha Inscripción',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustar el formato de texto a la izquierda para todas las celdas
        $sheet->getStyle('A:S')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:S1'); // Fusionar celdas desde A1 hasta L1
        $sheet->setAutoFilter('A2:S2'); // Aplicar estilo a la primera fila (encabezados)
        $sheet->getStyle('A1:S1')->applyFromArray([
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

        $sheet->getStyle('A2:S2')->applyFromArray([
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
        $lastColumnIndex = 'S'; // Última columna a la que deseas aplicar el ajuste

        while ($columnIndex <= $lastColumnIndex) {
            $dimension = $sheet->getColumnDimension($columnIndex);

            if ($columnIndex !== 'H' && $columnIndex !== 'I' && $columnIndex !== 'D' && $columnIndex !== 'G') {
                $dimension->setAutoSize(true);
            }

            // Ajustar el ancho de la columna comprimiéndola ligeramente
            $heading = $standardHeadings[ord($columnIndex) - ord('A')]; // Obtener el encabezado para la columna actual
            $dimension->setWidth(strlen($heading) + 4); // Ajustar según la longitud del encabezado

            $columnIndex++;
        }
    }
}
