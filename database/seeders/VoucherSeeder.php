<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Crear voucher especifico
        Voucher::create([
            'concepto_pago_id' => 1,
            'numero' => '2091020',
            'num_iden' => '75167077',
            'nombre_completo' => 'AQUINO VASQUEZ JOAN EDINSON',
            'monto' => 250.00,
            'fecha_pago' => '2026-02-02',
            'hora_pago' => '12:00:00',
            'cajero' => '123',
            'agencia' => '0987',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
