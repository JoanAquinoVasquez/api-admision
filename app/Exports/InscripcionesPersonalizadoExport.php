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

class InscripcionesPersonalizadoExport implements FromCollection, WithHeadings, WithStyles
{
    protected $gradoId;
    protected $programaId;
    protected $aperturado;
    protected $notasFilter;

    public function __construct($gradoId = null, $programaId = null, $aperturado = null, $notasFilter = null)
    {
        $this->gradoId = $gradoId;
        $this->programaId = $programaId;
        $this->aperturado = $aperturado;
        $this->notasFilter = $notasFilter;
    }

    public function collection()
    {
        $query = Inscripcion::with([
            'postulante',
            'programa.grado',
            'programa.facultad',
            'nota'
        ])
            ->join('programas', 'inscripcions.programa_id', '=', 'programas.id')
            ->join('grados', 'programas.grado_id', '=', 'grados.id')
            ->join('postulantes', 'inscripcions.postulante_id', '=', 'postulantes.id')
            ->orderByRaw("FIELD(grados.nombre, 'DOCTORADO', 'MAESTRÍA', 'SEGUNDA ESPECIALIDAD PROFESIONAL')")
            ->orderBy('programas.nombre')
            ->orderBy('postulantes.ap_paterno')
            ->orderBy('postulantes.ap_materno')
            ->orderBy('postulantes.nombres')
            ->select('inscripcions.*');

        // 1. Filtro de Grado Académico
        if ($this->gradoId && $this->gradoId !== 'all') {
            $query->where('programas.grado_id', $this->gradoId);
        }

        // 2. Filtro de Programa
        if ($this->programaId) {
            if (is_array($this->programaId)) {
                $query->whereIn('inscripcions.programa_id', $this->programaId);
            } else {
                $query->where('inscripcions.programa_id', $this->programaId);
            }
        }

        // 3. Filtro de Apertura
        if ($this->aperturado !== null && $this->aperturado !== '') {
            $query->where('programas.estado', $this->aperturado);
            if ($this->aperturado == 1) {
                $query->where('inscripcions.estado', 1);
            } else {
                $query->whereIn('inscripcions.estado', [0, 2, 3]);
            }
        }

        // 4. Filtro de Notas
        if ($this->notasFilter && is_array($this->notasFilter)) {
            foreach ($this->notasFilter as $filter) {
                if ($filter === 'con_cv') {
                    $query->whereHas('nota', fn($q) => $q->whereNotNull('cv'));
                } elseif ($filter === 'no_trajo_cv') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('nota')
                          ->orWhereHas('nota', fn($sq) => $sq->whereNull('cv'));
                    })
                    ->where('inscripcions.val_fisico', 0);
                } elseif ($filter === 'falta_evaluar') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('nota')
                          ->orWhereHas('nota', fn($sq) => $sq->whereNull('cv'));
                    })
                    ->where('inscripcions.val_fisico', 1);
                } elseif ($filter === 'con_entrevista') {
                    $query->whereHas('nota', fn($q) => $q->whereNotNull('entrevista'));
                } elseif ($filter === 'sin_entrevista') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('nota')
                          ->orWhereHas('nota', fn($sq) => $sq->whereNull('entrevista'));
                    });
                } elseif ($filter === 'con_examen') {
                    $query->whereHas('nota', fn($q) => $q->whereNotNull('examen'));
                } elseif ($filter === 'sin_examen') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('nota')
                          ->orWhereHas('nota', fn($sq) => $sq->whereNull('examen'));
                    });
                }
            }
        }

        $inscripciones = $query->get();

        $estadoMapping = [
            0 => 'Inhabilitado',
            1 => 'Activo',
            2 => 'Reservado',
            3 => 'Devolución',
        ];

        return $inscripciones->map(function ($inscripcion) use ($estadoMapping) {
            $cv = $inscripcion->nota->cv ?? null;
            $entrevista = $inscripcion->nota->entrevista ?? null;
            $examen = $inscripcion->nota->examen ?? null;

            $puntajeCv = is_numeric($cv) ? $cv : (($inscripcion->val_fisico == 1) ? 'FALTA EVALUAR' : 'NO TRAJO CV');
            $puntajeEntrevista = is_numeric($entrevista) ? $entrevista : 'NSP';
            $puntajeExamen = is_numeric($examen) ? $examen : 'NSP';

            return [
                'DNI' => $inscripcion->postulante->num_iden ?? 'N/A',
                'APELLIDOS Y NOMBRES COMPLETO' => ($inscripcion->postulante->ap_paterno ?? '') . ' ' . ($inscripcion->postulante->ap_materno ?? '') . ' ' . ($inscripcion->postulante->nombres ?? ''),
                'CORREO' => $inscripcion->postulante->email ?? 'N/A',
                'NUM. TELEFONO' => $inscripcion->postulante->celular ?? 'N/A',
                'GRADO' => $inscripcion->programa->grado->nombre ?? 'N/A',
                'PROGRAMA' => $inscripcion->programa->nombre ?? 'N/A',
                'PUNTAJE CV' => $puntajeCv,
                'PUNTAJE ENTREVISTA' => $puntajeEntrevista,
                'PUNTAJE EXAMEN' => $puntajeExamen,
                'ESTADO INSCRIPCIÓN' => $estadoMapping[$inscripcion->estado] ?? 'Desconocido',
                'APERTURA PROGRAMA' => $inscripcion->programa->estado == 1 ? 'APERTURADO' : 'NO APERTURADO',
            ];
        });
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo');
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "REPORTE PERSONALIZADO DE POSTULANTES - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo";
        return [
            [$programaHeading],
            [
                'DNI',
                'APELLIDOS Y NOMBRES COMPLETO',
                'CORREO',
                'NUM. TELEFONO',
                'GRADO',
                'PROGRAMA',
                'PUNTAJE CV',
                'PUNTAJE ENTREVISTA',
                'PUNTAJE EXAMEN',
                'ESTADO INSCRIPCIÓN',
                'APERTURA PROGRAMA',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:K1');
        $sheet->setAutoFilter('A2:K2');

        // Color de cabecera verde esmeralda para el reporte personalizado
        $primaryColor = '10b981';

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $primaryColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:K2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $primaryColor],
            ],
        ]);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
