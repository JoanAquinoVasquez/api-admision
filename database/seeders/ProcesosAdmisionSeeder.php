<?php

namespace Database\Seeders;

use App\Models\ProcesoAdmision;
use Illuminate\Database\Seeder;

class ProcesosAdmisionSeeder extends Seeder
{
    public function run(): void
    {
        $procesos = [
            ['codigo' => '2024-II', 'nombre' => 'Admisión 2024-II', 'estado' => ProcesoAdmision::ESTADO_CERRADO],
            ['codigo' => '2025-I', 'nombre' => 'Admisión 2025-I', 'estado' => ProcesoAdmision::ESTADO_CERRADO],
            ['codigo' => '2026-I', 'nombre' => 'Admisión 2026-I', 'estado' => ProcesoAdmision::ESTADO_CERRADO],
            ['codigo' => '2027-I', 'nombre' => 'Admisión 2027-I', 'estado' => ProcesoAdmision::ESTADO_PREPARACION],
        ];

        foreach ($procesos as $proceso) {
            ProcesoAdmision::updateOrCreate(
                ['codigo' => $proceso['codigo']],
                ['nombre' => $proceso['nombre'], 'estado' => $proceso['estado']]
            );
        }
    }
}
