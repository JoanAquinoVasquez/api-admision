<?php

namespace Tests\Feature;

use App\Mail\ProgramaInhabilitadoEmail;
use App\Models\Inscripcion;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Grado;
use App\Models\Facultad;
use App\Models\ConceptoPago;
use App\Models\Distrito;
use App\Models\Voucher;
use App\Services\ReservaDevolucionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InhabilitarProgramaEmailTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_queues_emails_when_programs_are_disabled()
    {
        Mail::fake();

        // 1. Obtener o crear entidades requeridas para cumplir con llaves foráneas
        $grado = Grado::first() ?? Grado::create(['nombre' => 'Doctorado', 'estado' => 1]);
        $facultad = Facultad::first() ?? Facultad::create(['nombre' => 'Facultad de Prueba', 'siglas' => 'FP', 'estado' => 1]);
        $conceptoPago = ConceptoPago::first() ?? ConceptoPago::create(['cod_concepto' => '1001', 'nombre' => 'Concepto Prueba', 'monto' => 200, 'estado' => 1]);
        $distrito = Distrito::first(); // Asumimos que los distritos están poblados por los seeders

        $distritoId = $distrito ? $distrito->id : 1;

        $randId = strval(rand(10000000, 99999999));
        $randVoucher = strval(rand(100000, 999999));

        $programa = Programa::create([
            'nombre' => 'Programa de Prueba de Inhabilitacion',
            'vacantes' => '15',
            'estado' => 1,
            'grado_id' => $grado->id,
            'facultad_id' => $facultad->id,
            'concepto_pago_id' => $conceptoPago->id,
        ]);

        $voucher = Voucher::create([
            'concepto_pago_id' => $conceptoPago->id,
            'numero' => $randVoucher,
            'num_iden' => $randId,
            'monto' => 200,
            'fecha_pago' => '2026-06-22',
            'hora_pago' => '09:00:00',
            'cajero' => '0001',
            'agencia' => '0001',
            'nombre_completo' => 'Prueba Laravel Email',
        ]);

        $postulante = Postulante::create([
            'distrito_id' => $distritoId,
            'nombres' => 'Prueba',
            'ap_paterno' => 'Laravel',
            'ap_materno' => 'Email',
            'email' => 'postulante.prueba@unprg.edu.pe',
            'tipo_doc' => 'DNI',
            'num_iden' => $randId,
            'fecha_nacimiento' => '1995-05-15',
            'sexo' => 'M',
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'estado' => 1,
        ]);

        $inscripcion = Inscripcion::create([
            'postulante_id' => $postulante->id,
            'programa_id' => $programa->id,
            'voucher_id' => $voucher->id,
            'codigo' => strval(rand(1000, 9999)),
            'val_digital' => 0,
            'val_fisico' => 0,
            'estado' => 1,
        ]);

        // 2. Ejecutar el servicio para inhabilitar el programa creado
        $service = app(ReservaDevolucionService::class);
        $service->inhabilitarInscripciones([$programa->id]);

        // 3. Validar que el estado del programa e inscripción cambió a 0 (Inhabilitado)
        $this->assertEquals(0, $programa->fresh()->estado);
        $this->assertEquals(0, $inscripcion->fresh()->estado);

        // 4. Validar que el correo fue encolado en segundo plano para el postulante
        Mail::assertQueued(ProgramaInhabilitadoEmail::class, function ($mail) use ($inscripcion) {
            return $mail->inscripcion->id === $inscripcion->id;
        });
    }
}

