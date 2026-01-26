<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grado::create([
            'nombre' => 'DOCTORADO',
        ]);

        Grado::create([
            'nombre' => 'MAESTRIA',
        ]); 

        Grado::create([
            'nombre' => 'SEGUNDA ESPECIALIDAD PROFESIONAL',
        ]);
    }
}
