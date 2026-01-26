<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ConceptoPago; // Asegúrate de importar el modelo ConceptoPago

class ConceptoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            [
                'cod_concepto' => '00000012',
                'nombre' => 'Inscripción de Maestría, Doctorado y Segunda Especialidad FCCBB',
                'monto' => 250.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00001005',
                'nombre' => 'Inscripción de Segunda Especialidad de FE',
                'monto' => 300.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000971',
                'nombre' => 'Inscripción de Segunda Especialidad de FIQUIA',
                'monto' => 50.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000970',
                'nombre' => 'Carpeta de Segunda Especialidad de FIQUIA',
                'monto' => 150.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000001',
                'nombre' => 'Matrícula de Maestría',
                'monto' => 250.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000001',
                'nombre' => 'Matrícula de Doctorado',
                'monto' => 300.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000003',
                'nombre' => 'Pensión de Maestría',
                'monto' => 500.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_concepto' => '00000003',
                'nombre' => 'Pensión de Doctorado',
                'monto' => 600.00,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($conceptos as $concepto) {
            ConceptoPago::create($concepto); // Crea registros usando el modelo
        }
    }
}
