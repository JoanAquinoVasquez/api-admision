<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nota>
 */
class NotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $cv = $this->faker->randomFloat(3, 0, 20);
        $entrevista = $this->faker->randomFloat(3, 0, 40);
        $examen = $this->faker->randomFloat(3, 0, 40);
        $final = $cv + $entrevista + $examen;

        return [
            'inscripcion_id' => Inscripcion::inRandomOrder()->first()->id,
            'cv' => $cv,
            'entrevista' => $entrevista,
            'examen' => $examen,
            'final' => $final,
        ];
    }
}
