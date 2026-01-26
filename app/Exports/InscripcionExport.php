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

class InscripcionExport implements FromCollection, WithHeadings, WithStyles
{
    // Enviados desde el controller
    protected $gradoId;
    protected $programaId;

    // Resividos por el constructor
    public function __construct($gradoId, $programaId)
    {
        $this->gradoId = $gradoId;
        $this->programaId = $programaId;
    }

    public function collection()
    {
        $query = Inscripcion::with([
            'postulante.distrito.provincia.departamento',
            'programa.grado',
            'programa.facultad',
        ]);

        // Aplicar filtros dinámicamente
        if ($this->gradoId) {
            $query->whereHas('programa.grado', function ($query) {
                $query->where('id', $this->gradoId);
            });
        }

        if ($this->programaId) {
            $query->where('programa_id', $this->programaId);
        }

        $inscripciones = $query->get();

        return $inscripciones->map(function ($inscripcion) {
            return [
                'ID' => $inscripcion->id,
                'Nombres' => $inscripcion->postulante->nombres,
                'Apellidos' => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno,
                'Correo' => $inscripcion->postulante->email,
                'Tipo Doc.' => $inscripcion->postulante->tipo_doc,
                'N° ID' => $inscripcion->postulante->num_iden,
                'Celular' => $inscripcion->postulante->celular,
                'Fecha de Nacimiento' => $inscripcion->postulante->fecha_nacimiento,
                'Sexo' => $inscripcion->postulante->sexo == 'M' ? 'Masculino' : 'Femenino',
                'Departamento' => $inscripcion->postulante->distrito->provincia->departamento->nombre ?? 'N/A',
                'Provincia' => $inscripcion->postulante->distrito->provincia->nombre ?? 'N/A',
                'Distrito' => $inscripcion->postulante->distrito->nombre ?? 'N/A',
                'Facultad' => $inscripcion->programa->facultad->siglas ?? 'N/A',
                'Grado' => $inscripcion->programa->grado->nombre ?? 'N/A',
                'Programa' => $inscripcion->programa->nombre ?? 'N/A',
                'Validad Digital' => $inscripcion->val_digital == 1 ? 'Validado'
                    : ($inscripcion->val_digital == 2 ? 'Observado' : 'Pendiente'),
                'Validad Física' => $inscripcion->val_fisico ? 'Validado' : 'Pendiente',
                'Observación' => $inscripcion->observacion,
                'Fecha de Inscripción' => $inscripcion->created_at,
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE PERSONAS INSCRITAS - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo"; // Encabezado especial
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
                'Validación Digital',
                'Validación Física',
                'Observación',
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
