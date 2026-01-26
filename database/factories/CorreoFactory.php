<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use App\Models\PreInscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Correo>
 */
class CorreoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pre_inscripcion_id' => PreInscripcion::inRandomOrder()->first()->id ?? null, // Asociar una pre-inscripción aleatoria, o null
            'inscripcion_id' => Inscripcion::inRandomOrder()->first()->id ?? null, // Asociar una inscripción aleatoria, o null
            'tipo' => $this->faker->randomElement(['Bienvenida', 'Confirmación', 'Rechazo', 'Notificación']), // Tipo de correo aleatorio
            'detalle' => $this->faker->sentence(), // Detalle del correo aleatorio
        ];
    }
}
