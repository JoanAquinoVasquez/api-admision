<?php

namespace Database\Factories;

use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inscripcion>
 */
class InscripcionFactory extends Factory
{
    public function definition(): array
    {
        // Obtener un postulante aleatorio
        $postulante = Postulante::inRandomOrder()->first();

        // Obtener el voucher asociado al postulante
        $voucher = Voucher::where('num_iden', $postulante->num_iden)
            ->where('concepto_pago_id', '!=', 4)
            ->first();

        // Obtener los programas relacionados con el concepto_pago del voucher
        $programas = Programa::where('concepto_pago_id', $voucher->concepto_pago_id)
            ->where('estado', true)
            ->get();

        // Seleccionar un programa aleatorio
        $programa = $programas->random();
        // Generar una fecha aleatoria en el último mes
        $startTimestamp = Carbon::create(2025, 2, 17, 0, 0, 0)->timestamp; // 17 de febrero 2025 00:00:00
        $endTimestamp = Carbon::create(2025, 3, 15, 23, 59, 59)->timestamp; // 15 de marzo 2025 23:59:59

        $randomTimestamp = random_int($startTimestamp, $endTimestamp);
        $createdAt = Carbon::createFromTimestamp($randomTimestamp);
        $updatedAt = (clone $createdAt)->addDays(rand(0, 5)); // Actualizado entre 0 y 5 días después

        return [
            'postulante_id' => $postulante->id,
            'programa_id'   => $programa->id,
            'voucher_id'    => $voucher->id,
            'codigo'        => $voucher->numero, // Código único de inscripción
            'val_digital'   => $this->faker->numberBetween(0, 2),  // Puede ser 0, 1 o 2
            'val_fisico'    => $this->faker->boolean(),  // Indica si la validación es física
            'observacion'   => null, // Observaciones, si las hubiera
            'created_at'    => $createdAt,
            'updated_at'    => $updatedAt,
        ];
    }
}
