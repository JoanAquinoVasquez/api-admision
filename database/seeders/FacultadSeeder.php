<?php

namespace Database\Seeders;

use App\Models\Facultad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facultades = [
            [
                'nombre' => 'FACULTAD DE INGENIERÍA QUÍMICA E INDUSTRIAS ALIMENTARIAS',
                'siglas' => 'FIQUIA',
            ],
            [
                'nombre' => 'FACULTAD DE INGENIERÍA CIVIL, DE SISTEMAS Y DE ARQUITECTURA',
                'siglas' => 'FICSA',
            ],
            [
                'nombre' => 'FACULTAD DE CIENCIAS ECONÓMICAS ADMINISTRATIVAS Y CONTABLES',
                'siglas' => 'FACEAC',
            ],
            [
                'nombre' => 'FACULTAD DE ENFERMERIA',
                'siglas' => 'FE',
            ],
            [
                'nombre' => 'FACULTAD DE INGENIERÍA MECÁNICA Y ELÉCTRICA',
                'siglas' => 'FIME',
            ],            
            [
                'nombre' => 'FACULTAD DE DERECHO Y CIENCIA POLÍTICA ',
                'siglas' => 'FDCP',
            ],
            [
                'nombre' => 'FACULTAD DE INGENIERÍA AGRICOLA',
                'siglas' => 'FIA',
            ],
            [
                'nombre' => 'FACULTAD DE CIENCIAS HISTÓRICO SOCIALES Y EDUCACIÓN',
                'siglas' => 'FACHSE',
            ],
            [
                'nombre' => 'FACULTAD DE CIENCIAS BIOLÓGICAS',
                'siglas' => 'FCCBB',
            ],
            [
                'nombre' => 'FACULTAD DE MEDICINA VETERINARIA',
                'siglas' => 'FMV',
            ],
            [
                'nombre' => 'FACULTAD DE AGRONOMÍA',
                'siglas' => 'FAG'
            ]
        ];

        foreach ($facultades as $facultad) {
            Facultad::create($facultad);
        }
    }
}
