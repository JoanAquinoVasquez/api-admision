<?php

namespace Database\Seeders;

use App\Models\ComisionAdmision;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComisionAdmisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comision = [
            [
                'nombres' => 'Juan',
                'ap_paterno' => 'Pérez',
                'ap_materno' => 'Gómez',
                'email' => 'arojasf@unprg.edu.pe',
                'telefono' => '987654321',
                'resumen_completo' => true,
                'estado' => true,
            ],
            [
                'facultad_id' => 2,
                'nombres' => 'María',
                'ap_paterno' => 'López',
                'ap_materno' => 'Martínez',
                'email' => 'frojasf@unprg.edu.pe',
                'telefono' => '987654322',
                'resumen_completo' => false,
                'estado' => true,
            ]
        ];

        foreach ($comision as $item) {
            ComisionAdmision::create($item);
        }
    }
}
