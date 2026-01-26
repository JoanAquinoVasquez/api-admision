<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BitacoraExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected ?string $fechaInicio;
    protected ?string $fechaFin;

    public function __construct(?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;
    }

    public function collection()
    {
        $query = Activity::latest();

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ]);
        }

        return $query->get()->map(function ($log) {
            $properties  = $log->properties->toArray();
            $user        = User::find($log->causer_id);

            $subject     = $properties['subject'] ?? [];
            $dataOld     = $properties['data_old'] ?? [];
            $dataNew     = $properties['data_new'] ?? [];
            $programaOld = $properties['programa_old'] ?? [];
            $programaNew = $properties['programa_new'] ?? [];
            $archivo     = $properties['archivo_modificado'] ?? null;

            $valorAntiguo = $valorNuevo = $progAntiguo = $progNuevo = null;

            if (!empty($dataOld) || !empty($dataNew)) {
                $valorAntiguo = json_encode($dataOld, JSON_UNESCAPED_UNICODE);
                $valorNuevo   = json_encode($dataNew, JSON_UNESCAPED_UNICODE);
            }

            if (!empty($programaOld) || !empty($programaNew)) {
                $progAntiguo  = $programaOld['nombre'] ?? $programaOld['nombre_programa'] ?? null;
                $progNuevo    = $programaNew['nombre'] ?? $programaNew['nombre_programa'] ?? null;
            }

            return [
                'Fecha'              => $log->created_at->format('Y-m-d H:i:s'),
                'Acción'             => $log->description,
                'Usuario'            => $user->name ?? 'Sistema',
                'Email'              => $user->email ?? null,
                'Postulante'         => isset($subject['nombres'])
                    ? trim($subject['nombres'] . ' ' . ($subject['ap_paterno'] ?? '') . ' ' . ($subject['ap_materno'] ?? ''))
                    : null,
                'Documento'          => $subject['num_iden'] ?? null,
                'Tipo Doc'           => $subject['tipo_doc'] ?? null,
                'Valor Antiguo'      => $valorAntiguo,
                'Valor Nuevo'        => $valorNuevo,
                'Programa Antiguo'   => $progAntiguo,
                'Programa Nuevo'     => $progNuevo,
                'Archivo Modificado' => $archivo,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Acción',
            'Usuario',
            'Email',
            'Postulante',
            'Documento',
            'Tipo Doc',
            'Valor Antiguo',
            'Valor Nuevo',
            'Programa Antiguo',
            'Programa Nuevo',
            'Archivo Modificado',
        ];
    }

    public function title(): string
    {
        return 'Bitácora de Auditoría Proceso de Admisión ' . config('admission.cronograma.periodo') . ' | EPG UNPRG';
    }

    /**
     * 📌 Estilos para la hoja de Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Cabecera en negrita
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '4F81BD'] // azul corporativo
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ],
        ];
    }

    /**
     * 📌 Ancho de columnas automático
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Fecha
            'B' => 20, // Acción
            'C' => 25, // Usuario
            'D' => 20, // Email
            'E' => 20, // Postulante
            'F' => 15, // Documento
            'G' => 15, // Tipo Doc
            'H' => 15, // Valor Antiguo
            'I' => 15, // Valor Nuevo
            'J' => 15, // Programa Antiguo
            'K' => 15, // Programa Nuevo
            'L' => 15, // Archivo Modificado
        ];
    }
}
