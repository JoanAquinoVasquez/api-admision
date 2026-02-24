<?php

namespace App\Exports;

use App\Models\Inscripcion;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InscripcionesFinalesExport implements FromCollection, WithHeadings, WithStyles
{

    public function collection()
    {
        /* $inscripciones = Inscripcion::with([
            'programa.grado',
            'programa.facultad',
            'nota'
        ])->get(); */

        $inscripciones = Inscripcion::with([
            'programa.grado',
            'programa.facultad',
            'nota',
            'postulante'
        ])
            ->where('programas.estado', 1)
            ->where('inscripcions.estado', 1)
            ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
            ->join('grados', 'programas.grado_id', '=', 'grados.id')
            ->join('postulantes', 'inscripcions.postulante_id', '=', 'postulantes.id')
            ->orderByRaw("FIELD(grados.nombre, 'DOCTORADO', 'MAESTRÍA', 'SEGUNDA ESPECIALIDAD PROFESIONAL')")
            ->orderBy('programas.nombre') // Ordena alfabéticamente los programas dentro del grado
            ->orderBy('postulantes.ap_paterno')
            ->orderBy('postulantes.ap_materno')
            ->orderBy('postulantes.nombres')
            ->select('inscripcions.*') // Para que los modelos funcionen correctamente al hacer `with()`
            ->get();

        return $inscripciones->map(function ($inscripcion) {
            return [
                'DNI' => $inscripcion->postulante->num_iden,
                'APELLIDOS Y NOMBRES COMPLETO' => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno . ' ' . $inscripcion->postulante->nombres,
                'CORREO' => $inscripcion->postulante->email,
                'NUM. TELEFONO' => $inscripcion->postulante->celular,
                'ID GRADO' => $inscripcion->programa->grado_id ?? 'N/A',
                'GRADO' => $inscripcion->programa->grado->nombre ?? 'N/A',
                'ID PROGRAMA' => $inscripcion->programa->id ?? 'N/A',
                'PROGRAMA' => $inscripcion->programa->nombre ?? 'N/A',
                'PUNTAJE CV' => $inscripcion->nota->cv ?? 'NSP',
                'PUNTAJE ENTREVISTA' => $inscripcion->nota->entrevista ?? 'NSP',
                'PUNTAJE EXAMEN' => $inscripcion->nota->examen ?? 'NSP',
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE PERSONAS APTAS - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo"; // Encabezado especial
        return [
            [$programaHeading], // Primera fila con encabezado especial
            [ // Segunda fila con encabezados estándar
                'DNI',
                'APELLIDOS Y NOMBRES COMPLETO',
                'CORREO',
                'NUM. TELEFONO',
                'ID GRADO',
                'GRADO',
                'ID PROGRAMA',
                'PROGRAMA',
                'PUNTAJE CV',
                'PUNTAJE ENTREVISTA',
                'PUNTAJE EXAMEN',
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

            if ($columnIndex !== 'E' && $columnIndex !== 'G' && $columnIndex !== 'K') {
                $dimension->setAutoSize(true);
            }

            // Ajustar el ancho de la columna comprimiéndola ligeramente
            $heading = $standardHeadings[ord($columnIndex) - ord('A')]; // Obtener el encabezado para la columna actual
            $dimension->setWidth(strlen($heading) + 4); // Ajustar según la longitud del encabezado

            $columnIndex++;
        }
    }
}
