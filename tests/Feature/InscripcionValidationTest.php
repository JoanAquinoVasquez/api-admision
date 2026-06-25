<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InscripcionValidationTest extends TestCase
{
    use DatabaseTransactions;

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

    /** @test */
    public function it_allows_enrolling_in_a_disabled_program_and_sets_status_to_0()
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Queue::fake();

        // 1. Obtener o crear entidades requeridas para cumplir con llaves foráneas
        $grado = \App\Models\Grado::first() ?? \App\Models\Grado::create(['nombre' => 'Doctorado', 'estado' => 1]);
        $facultad = \App\Models\Facultad::first() ?? \App\Models\Facultad::create(['nombre' => 'Facultad de Prueba', 'siglas' => 'FP', 'estado' => 1]);
        $conceptoPago = \App\Models\ConceptoPago::first() ?? \App\Models\ConceptoPago::create(['cod_concepto' => '1001', 'nombre' => 'Concepto Prueba', 'monto' => 200, 'estado' => 1]);
        $distrito = \App\Models\Distrito::first();
        $distritoId = $distrito ? $distrito->id : 1;

        $randId = strval(rand(10000000, 99999999));
        $randVoucher = strval(rand(100000, 999999));

        // Programa inhabilitado (estado = 0)
        $programa = \App\Models\Programa::create([
            'nombre' => 'Programa de Prueba Inhabilitado',
            'vacantes' => '15',
            'estado' => 0, // INHABILITADO
            'grado_id' => $grado->id,
            'facultad_id' => $facultad->id,
            'concepto_pago_id' => $conceptoPago->id,
        ]);

        $voucher = \App\Models\Voucher::create([
            'concepto_pago_id' => $conceptoPago->id,
            'numero' => $randVoucher,
            'num_iden' => $randId,
            'monto' => 200,
            'fecha_pago' => '2026-06-22',
            'hora_pago' => '09:00:00',
            'cajero' => '0001',
            'agencia' => '0001',
            'nombre_completo' => 'Prueba Postulante Inhabilitado',
            'estado' => 1,
        ]);

        $voucherFile = \Illuminate\Http\UploadedFile::fake()->create('voucher.pdf', 100, 'application/pdf');
        $docIdenFile = \Illuminate\Http\UploadedFile::fake()->create('dociden.pdf', 100, 'application/pdf');
        $cvFile = \Illuminate\Http\UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $fotoFile = \Illuminate\Http\UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/inscripcion', [
            'programa_id' => $programa->id,
            'distrito_id' => $distritoId,
            'nombres' => 'Juan',
            'ap_paterno' => 'Perez',
            'ap_materno' => 'Gomez',
            'email' => 'juan.perez@test.com',
            'tipo_doc' => 'DNI',
            'num_iden' => $randId,
            'fecha_nacimiento' => '1995-05-15',
            'sexo' => 'M',
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'cod_voucher' => $randVoucher,
            'rutaVoucher' => $voucherFile,
            'rutaDocIden' => $docIdenFile,
            'rutaCV' => $cvFile,
            'rutaFoto' => $fotoFile,
        ]);

        $response->assertStatus(201);

        // Validar que la inscripción se creó con estado = 0
        $inscripcion = \App\Models\Inscripcion::where('postulante_id', function ($query) use ($randId) {
            $query->select('id')->from('postulantes')->where('num_iden', $randId);
        })->first();

        $this->assertNotNull($inscripcion);
        $this->assertEquals(0, $inscripcion->fresh()->estado);
    }
}
