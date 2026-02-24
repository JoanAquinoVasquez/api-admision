<?php

namespace Database\Seeders;

use App\Models\PreInscripcion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PreInscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 50 registros de PreInscripcion con datos aleatorios
        PreInscripcion::factory(100)->create();
    }
}
