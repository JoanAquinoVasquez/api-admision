<?php

namespace Database\Factories;

use App\Models\Distrito;
use App\Models\Postulante;
use App\Models\Programa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PreInscripcion>
 */
class PreInscripcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'postulante_id' => null,  // Crea un postulante asociado
            'distrito_id' => Distrito::inRandomOrder()->first()->id,
            'programa_id' => Programa::inRandomOrder()->first()->id,
            'nombres' => $this->faker->firstName(),
            'ap_paterno' => $this->faker->lastName(),
            'ap_materno' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'tipo_doc' => $this->faker->randomElement(['DNI', 'CE', 'PASAPORTE']),
            'num_iden' => $this->faker->unique()->numerify('########'),
            'fecha_nacimiento' => $this->faker->date(),
            'sexo' => $this->faker->randomElement(['M', 'F']),
            'celular' => $this->faker->phoneNumber(),
            'uni_procedencia' => $this->faker->company(),
            'centro_trabajo' => $this->faker->company(),
            'cargo' => $this->faker->jobTitle(),
            'estado' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTimeBetween('2025-01-06', '2025-03-06'),
        ];
    }
}
