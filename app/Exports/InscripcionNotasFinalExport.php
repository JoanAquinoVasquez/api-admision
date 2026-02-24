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
use Illuminate\Support\Collection;

class InscripcionNotasFinalExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
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
            ->orderBy('programas.nombre')
            ->select('inscripcions.*')
            ->get();

        // Agrupar inscripciones por programa
        $grouped = $inscripciones->groupBy('programa_id');

        $finalData = new Collection();

        foreach ($grouped as $programaId => $inscripcionesPrograma) {
            // Calcular Nota Final
            $inscripcionesPrograma = $inscripcionesPrograma->map(function ($inscripcion) {
                $cv = is_numeric($inscripcion->nota->cv ?? null) ? $inscripcion->nota->cv : 0;
                $entrevista = is_numeric($inscripcion->nota->entrevista ?? null) ? $inscripcion->nota->entrevista : 0;
                $examen = is_numeric($inscripcion->nota->examen ?? null) ? $inscripcion->nota->examen : 0;
                $nota_final = $cv + $entrevista + $examen;
                $inscripcion->nota_final = $nota_final;
                return $inscripcion;
            });

            // Ordenar: primero por nota final descendente, luego por ap_paterno, ap_materno, nombres
            $inscripcionesPrograma = $inscripcionesPrograma->sort(function ($a, $b) {
                if ($a->nota_final == $b->nota_final) {
                    $comparePaterno = strcmp($a->postulante->ap_paterno, $b->postulante->ap_paterno);
                    if ($comparePaterno === 0) {
                        $compareMaterno = strcmp($a->postulante->ap_materno, $b->postulante->ap_materno);
                        if ($compareMaterno === 0) {
                            return strcmp($a->postulante->nombres, $b->postulante->nombres);
                        }
                        return $compareMaterno;
                    }
                    return $comparePaterno;
                }
                return $b->nota_final <=> $a->nota_final; // Nota final descendente
            })->values();

            // Asignar mérito (considerando empates)
            $merito = 1;
            $anteriorNota = null;
            foreach ($inscripcionesPrograma as $key => $inscripcion) {
                if (!is_null($anteriorNota) && $inscripcion->nota_final < $anteriorNota) {
                    $merito = $key + 1;
                }
                $inscripcion->merito = $merito;
                $anteriorNota = $inscripcion->nota_final;
            }

            // Determinar situación (vacante alcanzada o no)
            // $vacantes = $inscripcionesPrograma->first()->programa->vacantes ?? 0;
            // foreach ($inscripcionesPrograma as $index => $inscripcion) {
            //     $entrevista = $inscripcion->nota->entrevista ?? null;
            //     $examen = $inscripcion->nota->examen ?? null;

            //     if (!is_numeric($entrevista) || !is_numeric($examen)) {
            //         $situacion = 'NO ALCANZÓ VACANTE';
            //     } else {
            //         $situacion = ($index < $vacantes) ? 'ALCANZÓ VACANTE' : 'NO ALCANZÓ VACANTE';
            //     }

            //     $finalData->push([
            //         'MÉRITO' => $inscripcion->merito,
            //         'DNI' => $inscripcion->postulante->num_iden,
            //         'APELLIDOS Y NOMBRES COMPLETO' => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno . ' ' . $inscripcion->postulante->nombres,
            //         'CORREO' => $inscripcion->postulante->email,
            //         'NUM. TELEFONO' => $inscripcion->postulante->celular,
            //         'ID GRADO' => $inscripcion->programa->grado_id ?? 'N/A',
            //         'GRADO' => $inscripcion->programa->grado->nombre ?? 'N/A',
            //         'ID PROGRAMA' => $inscripcion->programa->id ?? 'N/A',
            //         'PROGRAMA' => $inscripcion->programa->nombre ?? 'N/A',
            //         'PUNTAJE CV' => is_numeric($inscripcion->nota->cv ?? null) ? $inscripcion->nota->cv : 'NSP',
            //         'PUNTAJE ENTREVISTA' => is_numeric($inscripcion->nota->entrevista ?? null) ? $inscripcion->nota->entrevista : 'NSP',
            //         'PUNTAJE EXAMEN' => is_numeric($inscripcion->nota->examen ?? null) ? $inscripcion->nota->examen : 'NSP',
            //         'NOTA FINAL' => number_format($inscripcion->nota_final, 2),
            //         'SITUACIÓN' => $situacion,
            //     ]);
            // }

            // NUEVA LÓGICA: Solo los que tienen las tres notas numéricas "alcanzan vacante"
            foreach ($inscripcionesPrograma as $index => $inscripcion) {
                $cv = $inscripcion->nota->cv ?? null;
                $entrevista = $inscripcion->nota->entrevista ?? null;
                $examen = $inscripcion->nota->examen ?? null;

                // NUEVA LÓGICA: Solo los que tienen las tres notas numéricas "alcanzan vacante"
                if (is_numeric($cv) && is_numeric($entrevista) && is_numeric($examen)) {
                    $situacion = 'ALCANZÓ VACANTE';
                } else {
                    $situacion = 'NO ALCANZÓ VACANTE';
                }

                $finalData->push([
                    'MÉRITO' => $inscripcion->merito,
                    'DNI' => $inscripcion->postulante->num_iden,
                    'APELLIDOS Y NOMBRES COMPLETO' => $inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno . ' ' . $inscripcion->postulante->nombres,
                    'CORREO' => $inscripcion->postulante->email,
                    'NUM. TELEFONO' => $inscripcion->postulante->celular,
                    'ID GRADO' => $inscripcion->programa->grado_id ?? 'N/A',
                    'GRADO' => $inscripcion->programa->grado->nombre ?? 'N/A',
                    'ID PROGRAMA' => $inscripcion->programa->id ?? 'N/A',
                    'PROGRAMA' => $inscripcion->programa->nombre ?? 'N/A',
                    'PUNTAJE CV' => is_numeric($cv) ? $cv : 'NSP',
                    'PUNTAJE ENTREVISTA' => is_numeric($entrevista) ? $entrevista : 'NSP',
                    'PUNTAJE EXAMEN' => is_numeric($examen) ? $examen : 'NSP',
                    'NOTA FINAL' => number_format($inscripcion->nota_final, 2),
                    'SITUACIÓN' => $situacion,
                ]);
            }
        }

        return $finalData;
    }

    public function headings(): array
    {
        $periodo = config('admission.cronograma.periodo'); // valor por defecto si no existe
        $timeActual = Carbon::now()->format('H:i:s d/m/Y');
        $programaHeading = "LISTA DE RESULTADOS - " . $timeActual . " | ESCUELA DE POSGRADO - UNPRG - ADMISIÓN $periodo"; // Encabezado especial
        return [
            [$programaHeading],
            [
                'MÉRITO',
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
                'NOTA FINAL',
                'SITUACIÓN',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('A1:N1');
        $sheet->setAutoFilter('A2:N2');
        $sheet->getStyle('A1:N1')->applyFromArray([
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
        ]);
        $sheet->getStyle('A2:N2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff'],
            ],
        ]);

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
