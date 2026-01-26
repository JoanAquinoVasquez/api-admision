<?php

namespace Database\Seeders;

use App\Models\Inscripcion;
use Illuminate\Database\Seeder;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use Carbon\Carbon;

class InscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Inscripcion::factory(1000)->create();
        // Obtener todos los postulantes
        $postulantes = Postulante::all();

        foreach ($postulantes as $postulante) {
            // Obtener un voucher válido para el postulante
            $voucher = Voucher::where('num_iden', $postulante->num_iden)
                ->where('concepto_pago_id', '!=', 4)
                ->first();

            if (!$voucher) {
                continue; // Si no tiene voucher válido, pasa al siguiente
            }

            // Obtener los programas asociados al concepto de pago del voucher
            $programas = Programa::where('concepto_pago_id', $voucher->concepto_pago_id)
                ->where('estado', true)
                ->get();

            if ($programas->isEmpty()) {
                continue; // Si no hay programas válidos, pasa al siguiente
            }

            // Seleccionar un programa aleatorio
            $programa = $programas->random();

            // Generar fechas aleatorias dentro del rango especificado
            $startTimestamp = Carbon::create(2025, 2, 17, 0, 0, 0)->timestamp;
            $endTimestamp = Carbon::create(2025, 3, 15, 23, 59, 59)->timestamp;

            $randomTimestamp = random_int($startTimestamp, $endTimestamp);
            $createdAt = Carbon::createFromTimestamp($randomTimestamp);
            $updatedAt = (clone $createdAt)->addDays(rand(0, 5));

            // Crear la inscripción asegurando que cada postulante tenga solo una
            Inscripcion::create([
                'postulante_id' => $postulante->id,
                'programa_id'   => $programa->id,
                'voucher_id'    => $voucher->id,
                'codigo'        => $voucher->numero, // Código único de inscripción
                'val_digital'   => rand(0, 2), // Puede ser 0, 1 o 2
                'val_fisico'    => (bool)rand(0, 1), // Indica si la validación es física
                'observacion'   => null, // Observaciones, si las hubiera
                'created_at'    => $createdAt,
                'updated_at'    => $updatedAt,
            ]);
        }
    }
}
