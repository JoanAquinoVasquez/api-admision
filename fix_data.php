<?php
try {
    $dni = '33300330';
    echo "Checking DNI: $dni\n";

    // 1. Postulante
    $p = App\Models\Postulante::where('num_iden', $dni)->first();
    if ($p) {
        echo "Postulante found: ID {$p->id}\n";
    } else {
        echo "Postulante NOT found. Creating...\n";
        $distrito = App\Models\Distrito::first();
        if (!$distrito) {
            $distrito = App\Models\Distrito::create(['id' => '140101', 'nombre' => 'Distrito P', 'provincia_id' => 1, 'ubigeo' => '140101']);
        }
        $p = App\Models\Postulante::create([
            'num_iden' => $dni,
            'nombres' => 'POSTULANTE',
            'ap_paterno' => 'TEST',
            'ap_materno' => 'HU09',
            'email' => 'test@example.com',
            'tipo_doc' => 'DNI',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'M',
            'celular' => '999888777',
            'distrito_id' => $distrito->id,
            'direccion' => 'Calle Test 123',
            'estado' => 1
        ]);
        echo "Postulante created: ID {$p->id}\n";
    }

    // 2. Voucher
    $v = App\Models\Voucher::where('num_iden', $dni)->first();
    if ($v) {
        echo "Voucher found: ID {$v->id}\n";
    } else {
        echo "Voucher NOT found. Creating...\n";
        $v = App\Models\Voucher::create([
            'num_iden' => $dni,
            'numero' => substr($dni, 0, 7),
            'concepto_pago_id' => 1,
            'monto' => 300.00,
            'fecha_pago' => now()->format('Y-m-d'),
            'hora_pago' => '10:00:00',
            'agencia' => '0987',
            'cajero' => '0001',
            'nombre_completo' => 'POSTULANTE TEST',
            'estado' => 0
        ]);
        echo "Voucher created: ID {$v->id}\n";
    }

    // 3. Inscripcion
    // Ensure programs exist
    $grado = App\Models\Grado::firstOrCreate(['nombre' => 'Maestría Cypress'], ['estado' => 1]);
    $facultad = App\Models\Facultad::firstOrCreate(['nombre' => 'FICSA'], ['siglas' => 'FICSA', 'estado' => 1]);
    $prog = App\Models\Programa::firstOrCreate(['nombre' => 'Programa Cypress'], [
        'grado_id' => $grado->id,
        'facultad_id' => $facultad->id,
        'concepto_pago_id' => 1,
        'estado' => 1,
        'vacantes' => 50
    ]);

    $i = App\Models\Inscripcion::where('postulante_id', $p->id)->first();
    if ($i) {
        echo "Inscripcion found: ID {$i->id}, Codigo: {$i->codigo}\n";
    } else {
        echo "Inscripcion NOT found. Creating...\n";
        $i = App\Models\Inscripcion::create([
            'postulante_id' => $p->id,
            'programa_id' => $prog->id,
            'voucher_id' => $v->id,
            'codigo' => substr($dni, 0, 7), // Ensure 7 chars
            'val_digital' => 0,
            'val_fisico' => 0,
            'estado' => 1
        ]);
        echo "Inscripcion created: ID {$i->id}\n";

        // Create documents
        foreach (['DNI', 'CV', 'Foto', 'Constancia'] as $tipo) {
            App\Models\Documento::firstOrCreate([
                'postulante_id' => $p->id,
                'tipo' => $tipo
            ], [
                'nombre_archivo' => 'test_' . strtolower($tipo) . '.pdf',
                'url' => 'http://example.com/test.pdf',
                'estado' => 1
            ]);
        }
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
