<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Programa>
 */
class ProgramaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facultad_id' => \App\Models\Facultad::factory(),
            'grado_id' => \App\Models\Grado::factory(),
            'concepto_pago_id' => \App\Models\ConceptoPago::factory(),
            'docente_id' => \App\Models\Docente::factory(),
            'nombre' => $this->faker->word(),
            'vacantes' => $this->faker->numberBetween(1, 100),
        ];
    }
}
