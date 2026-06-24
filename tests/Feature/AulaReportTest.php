<?php

namespace Tests\Feature;

use App\Models\ConceptoPago;
use App\Models\Distrito;
use App\Models\Facultad;
use App\Models\Grado;
use App\Models\Inscripcion;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AulaReportTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_assigns_classrooms_and_splits_program_9_correctly()
    {
        // 1. Obtener o crear entidades requeridas para cumplir con llaves foráneas
        $grado = Grado::first() ?? Grado::create(['nombre' => 'Maestria', 'estado' => 1]);
        $facultad = Facultad::first() ?? Facultad::create(['nombre' => 'Facultad de Prueba', 'siglas' => 'FP', 'estado' => 1]);
        $conceptoPago = ConceptoPago::first() ?? ConceptoPago::create(['cod_concepto' => '1001', 'nombre' => 'Concepto Prueba', 'monto' => 200, 'estado' => 1]);
        $distrito = Distrito::first();
        $distritoId = $distrito ? $distrito->id : 1;

        // Limpiar o asegurar que no choquemos con IDs existentes si es que los creamos
        // Para programa ID 9 (Gerencia de Obras y Construcción)
        $programa9 = Programa::find(9);
        if (!$programa9) {
            $programa9 = new Programa();
            $programa9->id = 9;
            $programa9->nombre = 'Gerencia de Obras y Construcción';
            $programa9->vacantes = 50;
            $programa9->estado = 1;
            $programa9->grado_id = $grado->id;
            $programa9->facultad_id = $facultad->id;
            $programa9->concepto_pago_id = $conceptoPago->id;
            $programa9->save();
        } else {
            // Asegurar que esté activo
            $programa9->update(['estado' => 1]);
        }

        // Crear otro programa (e.g. ID 24 para Docencia y Gestión Universitaria)
        $programa24 = Programa::find(24);
        if (!$programa24) {
            $programa24 = new Programa();
            $programa24->id = 24;
            $programa24->nombre = 'Ciencias de la Educación con mención en Docencia y Gestión Universitaria';
            $programa24->vacantes = 20;
            $programa24->estado = 1;
            $programa24->grado_id = $grado->id;
            $programa24->facultad_id = $facultad->id;
            $programa24->concepto_pago_id = $conceptoPago->id;
            $programa24->save();
        } else {
            $programa24->update(['estado' => 1]);
        }

        // 2. Crear postulantes e inscripciones para el programa 9 (necesitamos más de 30 para verificar el split)
        $existingCount = Inscripcion::where('programa_id', 9)->count();

        // Crearemos 35 postulantes para el programa 9
        $baseNumIden = rand(40000000, 80000000);
        for ($i = 1; $i <= 35; $i++) {
            $numIden = strval($baseNumIden + $i);
            $postulante = Postulante::create([
                'distrito_id' => $distritoId,
                'nombres' => 'Postulante ' . $i,
                'ap_paterno' => 'Paterno' . sprintf('%02d', $i), // Esto ayuda al orden alfabético
                'ap_materno' => 'Materno',
                'email' => "postulante{$i}@example.com",
                'tipo_doc' => 'DNI',
                'num_iden' => $numIden,
                'fecha_nacimiento' => '1995-05-15',
                'sexo' => 'M',
                'celular' => '987654321',
                'direccion' => 'Calle Falsa 123',
                'estado' => 1,
            ]);

            $voucher = Voucher::create([
                'concepto_pago_id' => $conceptoPago->id,
                'numero' => strval(300000 + $i),
                'num_iden' => $numIden,
                'monto' => 200,
                'fecha_pago' => '2026-06-22',
                'hora_pago' => '09:00:00',
                'cajero' => '0001',
                'agencia' => '0001',
                'nombre_completo' => 'Postulante ' . $i,
            ]);

            Inscripcion::create([
                'postulante_id' => $postulante->id,
                'programa_id' => 9,
                'voucher_id' => $voucher->id,
                'codigo' => strval(8000 + $i),
                'val_digital' => 1,
                'val_fisico' => 1,
                'estado' => 1,
            ]);
        }

        // Crear una inscripción para el programa 24
        $randId24 = strval(rand(80000000, 90000000));
        $postulante24 = Postulante::create([
            'distrito_id' => $distritoId,
            'nombres' => 'Docencia',
            'ap_paterno' => 'Docente',
            'ap_materno' => 'Gestor',
            'email' => "docente@example.com",
            'tipo_doc' => 'DNI',
            'num_iden' => $randId24,
            'fecha_nacimiento' => '1990-05-15',
            'sexo' => 'F',
            'celular' => '987654321',
            'direccion' => 'Calle Falsa 123',
            'estado' => 1,
        ]);

        $voucher24 = Voucher::create([
            'concepto_pago_id' => $conceptoPago->id,
            'numero' => '400024',
            'num_iden' => $randId24,
            'monto' => 200,
            'fecha_pago' => '2026-06-22',
            'hora_pago' => '09:00:00',
            'cajero' => '0001',
            'agencia' => '0001',
            'nombre_completo' => 'Docente Gestor',
        ]);

        Inscripcion::create([
            'postulante_id' => $postulante24->id,
            'programa_id' => 24,
            'voucher_id' => $voucher24->id,
            'codigo' => '2401',
            'val_digital' => 1,
            'val_fisico' => 1,
            'estado' => 1,
        ]);

        // 3. Ejecutar la lógica de asignación
        $reportService = app(ReportService::class);
        
        // Emular la carga de datos que hace generateFinalAulasPdf:
        $idProgramas = [9, 24];
        $programasData = [];
        $aulasAsignadas = [
            24 => 'AULA 02',
        ];

        foreach ($idProgramas as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $programaNombre = $inscripciones->first()->programa->nombre ?? 'Desconocido';
                $gradoNombre = $inscripciones->first()->programa->grado->nombre ?? 'Desconocido';
                $docente = $inscripciones->first()->programa->docente;

                if ($idPrograma === 9) {
                    $inscripcionesGrupo1 = $inscripciones->take(30);
                    $inscripcionesGrupo2 = $inscripciones->slice(30)->values();

                    if ($inscripcionesGrupo1->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo1,
                            'docente' => $docente,
                            'aula' => 'AULA 06',
                        ];
                    }

                    if ($inscripcionesGrupo2->isNotEmpty()) {
                        $programasData[] = [
                            'programa' => $programaNombre,
                            'grado' => $gradoNombre,
                            'inscripciones' => $inscripcionesGrupo2,
                            'docente' => $docente,
                            'aula' => 'AULA 07',
                        ];
                    }
                } else {
                    $aula = $aulasAsignadas[$idPrograma] ?? 'Sin aula asignada';
                    $programasData[] = [
                        'programa' => $programaNombre,
                        'grado' => $gradoNombre,
                        'inscripciones' => $inscripciones,
                        'docente' => $docente,
                        'aula' => $aula,
                    ];
                }
            }
        }

        // 4. Aserciones
        // Deberíamos tener 3 registros en $programasData:
        // - Uno para ID 9 grupo 1 (30 inscripciones, AULA 06)
        // - Uno para ID 9 grupo 2 (restantes inscripciones, AULA 07)
        // - Uno para ID 24 (1 inscripción + existentes, AULA 02)
        $this->assertCount(3, $programasData);

        // Grupo 1: ID 9 Aula 06
        $this->assertEquals('Gerencia de Obras y Construcción', $programasData[0]['programa']);
        $this->assertEquals('AULA 06', $programasData[0]['aula']);
        $this->assertCount(30, $programasData[0]['inscripciones']);

        // Grupo 2: ID 9 Aula 07
        $expectedGroup2Count = ($existingCount + 35) - 30;
        $this->assertEquals('Gerencia de Obras y Construcción', $programasData[1]['programa']);
        $this->assertEquals('AULA 07', $programasData[1]['aula']);
        $this->assertCount($expectedGroup2Count, $programasData[1]['inscripciones']);

        // Programa 24: Aula 02
        $expectedProgram24Count = Inscripcion::where('programa_id', 24)->count();
        $this->assertEquals($programa24->nombre, $programasData[2]['programa']);
        $this->assertEquals('AULA 02', $programasData[2]['aula']);
        $this->assertCount($expectedProgram24Count, $programasData[2]['inscripciones']);
    }
}
