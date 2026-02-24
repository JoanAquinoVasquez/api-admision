<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombres' => $this->faker->firstName(),
            'ap_paterno' => $this->faker->lastName(),
            'ap_materno' => $this->faker->lastName(),
            'dni' => $this->faker->unique()->numberBetween(10000000, 99999999),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password()
        ];
    }
}
