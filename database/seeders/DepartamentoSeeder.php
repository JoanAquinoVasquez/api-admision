<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departamentos = [
            ['id' => 1, 'nombre' => 'AMAZONAS'],
            ['id' => 2, 'nombre' => 'ANCASH'],
            ['id' => 3, 'nombre' => 'APURIMAC'],
            ['id' => 4, 'nombre' => 'AREQUIPA'],
            ['id' => 5, 'nombre' => 'AYACUCHO'],
            ['id' => 6, 'nombre' => 'CAJAMARCA'],
            ['id' => 7, 'nombre' => 'CALLAO'],
            ['id' => 8, 'nombre' => 'CUSCO'],
            ['id' => 9, 'nombre' => 'HUANCAVELICA'],
            ['id' => 10, 'nombre' => 'HUANUCO'],
            ['id' => 11, 'nombre' => 'ICA'],
            ['id' => 12, 'nombre' => 'JUNIN'],
            ['id' => 13, 'nombre' => 'LA LIBERTAD'],
            ['id' => 14, 'nombre' => 'LAMBAYEQUE'],
            ['id' => 15, 'nombre' => 'LIMA'],
            ['id' => 16, 'nombre' => 'LORETO'],
            ['id' => 17, 'nombre' => 'MADRE DE DIOS'],
            ['id' => 18, 'nombre' => 'MOQUEGUA'],
            ['id' => 19, 'nombre' => 'PASCO'],
            ['id' => 20, 'nombre' => 'PIURA'],
            ['id' => 21, 'nombre' => 'PUNO'],
            ['id' => 22, 'nombre' => 'SAN MARTIN'],
            ['id' => 23, 'nombre' => 'TACNA'],
            ['id' => 24, 'nombre' => 'TUMBES'],
            ['id' => 25, 'nombre' => 'UCAYALI'],
        ];


        Departamento::insert($departamentos);
    }
}
