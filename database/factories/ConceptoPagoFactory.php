<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConceptoPago>
 */
class ConceptoPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cod_concepto' => $this->faker->unique()->bothify('CP-###??'),
            'nombre' => $this->faker->word(),
            'monto' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
