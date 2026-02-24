<?php

namespace Database\Factories;

use App\Models\ConceptoPago;
use App\Models\Distrito;
use App\Models\Postulante;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostulanteFactory extends Factory
{
    public function definition(): array
    {
        $vouchers = Voucher::where('estado', true)->where('concepto_pago_id', '!=', 4)->get();

        $voucher = $vouchers->random();
        
        $voucher->update(['estado' => false]);
        return [
            'distrito_id'      => Distrito::inRandomOrder()->first()->id,
            'nombres'          => $this->faker->firstName(),
            'ap_paterno'       => $this->faker->lastName(),
            'ap_materno'       => $this->faker->lastName(),
            'email'            => $this->faker->safeEmail(),
            'tipo_doc'         => $this->faker->randomElement(['DNI', 'CE', 'PASAPORTE']),
            'num_iden'         => $voucher->num_iden, // Generamos un 'num_iden' único
            'fecha_nacimiento' => $this->faker->date(),
            'sexo'             => $this->faker->randomElement(['M', 'F']),
            'celular'          => $this->faker->phoneNumber(),
            'direccion'        => $this->faker->address(),
            'estado'           => $this->faker->boolean(),
        ];
    }
}
