<?php

namespace App\Imports;

use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\Postulante;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log; // Permite importaciones en segundo plano
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// Permite registrar información en el log

// Permite leer el archivo en partes

class NotasExamenImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Buscar postulante por DNI
        $postulante = Postulante::where('num_iden', $row['dni'])->first();
        $id_programa_excel = $row['id_prog'];

        if (!$postulante) {
            return null; // No existe el postulante
        }

        // Buscar inscripción
        $inscripcion = Inscripcion::where('postulante_id', $postulante->id)->first();

        if (!$inscripcion) {
            return null; // No existe inscripción
        }

        // Validar que el programa coincida
        if ($inscripcion->programa_id != $id_programa_excel) {
            return null; // No actualizamos ni creamos notas si el programa no coincide
        }

        // Buscar si ya existe una nota
        $nota = Nota::where('inscripcion_id', $inscripcion->id)->first();

        if ($nota) {
            // Si ya existe, actualizar solo el examen
            $nota->examen = $row['puntaje'] ?? null;
            $nota->save();
        } else {
            // Si no existe, crear una nueva

            return new Nota([
                'inscripcion_id' => $inscripcion->id,
                'cv' => null,
                'entrevista' => null,
                'examen' => $row['puntaje'] ?? null,
                'final' => null,
                'estado' => true,
            ]);
        }

        return null; // No se necesita devolver nada si fue solo actualización
    }

    // Permite procesar el archivo en partes para mejorar rendimiento
    public function chunkSize(): int
    {
        return 500; // Ajusta el tamaño del lote según sea necesario
    }
}
