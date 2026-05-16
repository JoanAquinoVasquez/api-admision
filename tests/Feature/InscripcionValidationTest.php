<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InscripcionValidationTest extends TestCase
{
    /** @test */
    public function it_fails_if_dni_is_not_8_digits()
    {
        $response = $this->postJson('/inscripcion', [
            'programa_id' => 1,
            'distrito_id' => 1278,
            'nombres' => 'QA',
            'ap_paterno' => 'Automation',
            'ap_materno' => 'Test',
            'email' => 'qa@test.com',
            'tipo_doc' => 'DNI',
            'num_iden' => '1234567890', // 10 caracteres (DEBE FALLAR)
            'fecha_nacimiento' => '1995-05-15',
            'sexo' => 'M',
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'cod_voucher' => '123456'
        ]);

        // Verificamos que devuelva un error de validacion (422)
        $response->assertStatus(422);
        
        // Verificamos que el error sea especificamente en el campo num_iden
        $response->assertJsonValidationErrors(['num_iden']);
    }

    /** @test */
    public function it_fails_if_voucher_is_too_large()
    {
        // Simulamos un archivo PDF de 11MB (que debe fallar porque el limite es 10MB)
        $file = \Illuminate\Http\Testing\File::create('voucher.pdf', 11264); // 11MB

        $response = $this->postJson('/inscripcion', [
            'programa_id' => 1,
            'distrito_id' => 1278,
            'nombres' => 'QA',
            'ap_paterno' => 'Automation',
            'ap_materno' => 'Test',
            'email' => 'qa@test.com',
            'tipo_doc' => 'DNI',
            'num_iden' => '12345678',
            'fecha_nacimiento' => '1995-05-15',
            'sexo' => 'M',
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'cod_voucher' => '123456',
            'rutaVoucher' => $file
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rutaVoucher']);
    }
}
