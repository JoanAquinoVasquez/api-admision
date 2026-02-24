<?php

namespace App\Exports;

use App\Models\PreInscripcion;
use App\Models\Voucher;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PreInscripcionExport implements FromCollection, WithHeadings, WithStyles
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
        $query = PreInscripcion::with([
            'distrito.provincia.departamento',
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

        $preinscripciones = $query->get();

        // Obtener los num_iden de las preinscripciones
        $numIdens = $preinscripciones->pluck('num_iden');

        // Consultar los vouchers relacionados con los num_iden
        $vouchers = Voucher::whereIn('num_iden', $numIdens)->get()->keyBy('num_iden');

        return $preinscripciones->map(function ($preinscripcion)  use ($vouchers) {
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
                'Inscripcion' => $preinscripcion->postulante_id ? 'SI' : 'NO',
                'Pago' => $vouchers->has($preinscripcion->num_iden) ? 'SI' : 'NO', // Nueva columna
                'Fecha de Inscripción' => $preinscripcion->created_at,

            ];
        });
    }

    public function headings(): array
    {
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE PERSONAS PRE-INSCRITAS - " . $timeActual; // Encabezado especial
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
                'Inscripcion',
                'Pago',
                'Fecha Inscripción',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustar el formato de texto a la izquierda para todas las celdas
        $sheet->getStyle('A:U')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:U1'); // Fusionar celdas desde A1 hasta L1
        $sheet->setAutoFilter('A2:U2'); // Aplicar estilo a la primera fila (encabezados)
        $sheet->getStyle('A1:U1')->applyFromArray([
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

        $sheet->getStyle('A2:U2')->applyFromArray([
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
        $lastColumnIndex = 'T'; // Última columna a la que deseas aplicar el ajuste

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
