<?php

namespace Database\Factories;

use App\Models\ConceptoPago;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        // Traer los conceptos válidos
        $conceptos = ConceptoPago::whereIn('cod_concepto', [
            '00000012',
            '00001005',
            '00000971',
            '00000970',
        ])->get()->keyBy('cod_concepto');

        // Elegir tipo de voucher a crear
        $tipo = $this->faker->randomElement(['simple', 'pareja']);

        if ($tipo === 'simple') {
            // Solo puede ser 00000012 o 00001005
            $concepto = $this->faker->randomElement([
                $conceptos['00000012'],
                $conceptos['00001005'],
            ]);

            return [
                'concepto_pago_id' => $concepto->id,
                'numero'           => $this->faker->numerify('#######'),
                'num_iden'         => $this->faker->unique()->numerify('########'),
                'nombre_completo'  => $this->faker->name(),
                'monto'            => $concepto->monto,
                'fecha_pago'       => $this->faker->date(),
                'hora_pago'        => $this->faker->time(),
                'cajero'           => $this->faker->numerify('####'),
                'agencia'          => $this->faker->numerify('####'),
                'estado'           => true,
            ];
        }

        // Si es pareja, siempre se crean 00000971 + 00000970
        $num_iden = $this->faker->unique()->numerify('########');

        $concepto71 = $conceptos['00000971'];
        $concepto70 = $conceptos['00000970'];

        // Crear el segundo registro manualmente (uno lo devuelve, el otro lo guarda)
        Voucher::create([
            'concepto_pago_id' => $concepto70->id,
            'numero'           => $this->faker->numerify('#######'),
            'num_iden'         => $num_iden,
            'nombre_completo'  => $this->faker->name(),
            'monto'            => $concepto70->monto,
            'fecha_pago'       => $this->faker->date(),
            'hora_pago'        => $this->faker->time(),
            'cajero'           => $this->faker->numerify('####'),
            'agencia'          => $this->faker->numerify('####'),
            'estado'           => true,
        ]);

        // Retornar el 00000971 (o podríamos al revés, da igual porque ambos quedan en BD)
        return [
            'concepto_pago_id' => $concepto71->id,
            'numero'           => $this->faker->numerify('#######'),
            'num_iden'         => $num_iden,
            'nombre_completo'  => $this->faker->name(),
            'monto'            => $concepto71->monto,
            'fecha_pago'       => $this->faker->date(),
            'hora_pago'        => $this->faker->time(),
            'cajero'           => $this->faker->numerify('####'),
            'agencia'          => $this->faker->numerify('####'),
            'estado'           => true,
        ];
    }
}
