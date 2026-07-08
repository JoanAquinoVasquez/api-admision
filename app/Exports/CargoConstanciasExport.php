<?php

namespace App\Exports;

use App\Models\Programa;
use App\Models\Inscripcion;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CargoConstanciasExport implements WithMultipleSheets
{
    protected $gradoId;
    protected $programaId;

    public function __construct($gradoId = null, $programaId = null)
    {
        $this->gradoId = $gradoId;
        $this->programaId = $programaId;
    }

    public function sheets(): array
    {
        // 1. Obtener programas activos filtrados por grado y/o programa si es que se seleccionaron
        $query = Programa::with(['grado', 'facultad'])
            ->where('estado', 1);

        if ($this->gradoId) {
            $query->where('grado_id', $this->gradoId);
        }

        if ($this->programaId) {
            $query->where('id', $this->programaId);
        }

        $programas = $query->get();

        $sheets = [];

        foreach ($programas as $programa) {
            // Obtener ingresantes admitidos para este programa (los que tienen las tres notas registradas)
            $inscripciones = Inscripcion::with(['postulante'])
                ->where('inscripcions.estado', 1)
                ->where('programa_id', $programa->id)
                ->whereHas('nota', function ($q) {
                    $q->whereNotNull('cv')
                      ->whereNotNull('entrevista')
                      ->whereNotNull('examen');
                })
                ->get();

            // Ordenar alfabéticamente por apellidos y nombres
            $inscripciones = $inscripciones->sort(function ($a, $b) {
                $comparePaterno = strcmp($a->postulante->ap_paterno, $b->postulante->ap_paterno);
                if ($comparePaterno === 0) {
                    $compareMaterno = strcmp($a->postulante->ap_materno, $b->postulante->ap_materno);
                    if ($compareMaterno === 0) {
                        return strcmp($a->postulante->nombres, $b->postulante->nombres);
                    }
                    return $compareMaterno;
                }
                return $comparePaterno;
            })->values();

            // Solo agregar hoja al reporte si el programa tiene al menos 1 ingresante
            if ($inscripciones->count() > 0) {
                $sheets[] = new CargoConstanciasProgramSheet($programa, $inscripciones);
            }
        }

        return $sheets;
    }
}
