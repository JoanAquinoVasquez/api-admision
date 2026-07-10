<?php

namespace App\Exports;

use App\Models\Documento;
use App\Models\Inscripcion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InscripcionesInhabilitadasExport implements FromCollection, WithHeadings, WithStyles
{
    protected $estados;
    protected $titulo;

    public function __construct(array $estados = [0, 2, 3], string $titulo = 'GENERAL')
    {
        $this->estados = $estados;
        $this->titulo = $titulo;
    }

    public function collection()
    {
        $inscripciones = Inscripcion::whereIn('estado', $this->estados)
            ->with(['postulante', 'programa.grado'])
            ->get();

        return $inscripciones->map(function ($inscripcion) {
            // Verificar si el postulante tiene un documento de tipo 'Voucher'
            $documento = Documento::where([
                'tipo' => 'Voucher',
                'postulante_id' => $inscripcion->postulante_id
            ])->first();

            $estadoText = match ((int)$inscripcion->estado) {
                0 => 'Pendiente',
                2 => 'Reserva',
                3 => 'Devolución',
                default => 'Otro',
            };

            return [
                'ID' => $inscripcion->id,
                'N. Identidad' => $inscripcion->postulante->num_iden ?? '',
                'Nombres Completo' => $inscripcion->postulante ? ($inscripcion->postulante->nombres . ' ' . $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno) : '',
                'Correo' => $inscripcion->postulante->email ?? '',
                'Celular' => $inscripcion->postulante->celular ?? '',
                'Grado' => $inscripcion->programa->grado->nombre ?? '',
                'Programa' => $inscripcion->programa->nombre ?? '',
                'N. Voucher' => $inscripcion->codigo ?? '',
                'URL Voucher' => $documento ? $documento->url : 'No disponible',
                'Estado' => $estadoText,
                'Fecha de Inscripción' => $inscripcion->created_at,
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo');
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE INSCRIPCIONES - MODO " . mb_strtoupper($this->titulo) . " - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo";
        
        return [
            [$programaHeading],
            [
                'ID',
                'N. Identidad',
                'Nombres Completo',
                'Correo',
                'Telefono',
                'Grado',
                'Programa',
                'N. Voucher',
                'URL Voucher',
                'Estado',
                'Fecha de Inscripción',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:K1');
        $sheet->setAutoFilter('A2:K2');
        
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getStyle('A2:K2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff'],
            ],
        ]);

        $standardHeadings = $this->headings()[1];
        $columnIndex = 'A';
        $lastColumnIndex = 'K';

        while ($columnIndex <= $lastColumnIndex) {
            $dimension = $sheet->getColumnDimension($columnIndex);
            $dimension->setAutoSize(true);
            $heading = $standardHeadings[ord($columnIndex) - ord('A')];
            $dimension->setWidth(strlen($heading) + 4);
            $columnIndex++;
        }
    }
}
