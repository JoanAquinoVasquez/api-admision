<?php
            try {
                $dni = '33300330';

                $postulante = App\Models\Postulante::where('num_iden', $dni)->first();
                if ($postulante) {
                    App\Models\Inscripcion::where('postulante_id', $postulante->id)->delete();
                    App\Models\Documento::where('postulante_id', $postulante->id)->delete();
                }
                App\Models\Voucher::where('num_iden', $dni)->delete();

                $distrito = App\Models\Distrito::first();
                $distrito_id = $distrito ? $distrito->id : 1; 
                
                if (!$distrito) {
                     $distrito = App\Models\Distrito::create(['id' => '140101', 'nombre' => 'Distrito Test', 'provincia_id' => 1, 'ubigeo' => '140101']); 
                     $distrito_id = $distrito->id;
                }
                $grado = App\Models\Grado::firstOrCreate(['nombre' => 'Maestría Cypress'], ['estado' => 1]);
                $facultad = App\Models\Facultad::firstOrCreate(['nombre' => 'FICSA'], ['siglas' => 'FICSA', 'estado' => 1]);
                $concepto = App\Models\ConceptoPago::first();
                $programa = App\Models\Programa::firstOrCreate(['nombre' => 'Programa Cypress'], [
                    'grado_id' => $grado->id,
                    'facultad_id' => $facultad->id,
                    'concepto_pago_id' => $concepto ? $concepto->id : 1,
                    'estado' => 1,
                    'vacantes' => 50
                ]);

                $postulante = App\Models\Postulante::updateOrCreate(['num_iden' => $dni], [
                    'nombres' => 'POSTULANTE',
                    'ap_paterno' => 'TEST',
                    'ap_materno' => 'HU10',
                    'email' => 'jaquinov@unprg.edu.pe',
                    'tipo_doc' => 'DNI',
                    'fecha_nacimiento' => '1990-01-01',
                    'sexo' => 'M',
                    'celular' => '999888777',
                    'distrito_id' => $distrito_id,
                    'direccion' => 'Calle Test 123',
                    'estado' => 1
                ]);

                $voucher = App\Models\Voucher::create([
                    'num_iden' => $dni,
                    'numero' => substr($dni, 0, 7),
                    'concepto_pago_id' => $programa->concepto_pago_id,
                    'monto' => 250.00,
                    'fecha_pago' => now()->format('Y-m-d'),
                    'hora_pago' => '10:00:00',
                    'agencia' => '0987',
                    'cajero' => '0001',
                    'nombre_completo' => 'POSTULANTE TEST',
                    'estado' => 0
                ]);

                $inscripcion = App\Models\Inscripcion::updateOrCreate(['postulante_id' => $postulante->id], [
                    'programa_id' => $programa->id,
                    'voucher_id' => $voucher->id,
                    'codigo' => substr($dni, 0, 7), 
                    'val_digital' => 1,
                    'val_fisico' => 0,
                    'estado' => 1
                ]);

                // 1. Crear directorio y archivo dummy físico para la foto
                $storagePath = storage_path('app/public/fotos');
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }
                
                // Imagen dummy 1x1 pixel jpg base64
                $dummyImgInfo = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
                $dummyImgData = base64_decode($dummyImgInfo);
                file_put_contents($storagePath . '/test_foto.jpg', $dummyImgData);

                // 2. Crear registros de documentos (Excluyendo Foto)
                foreach(['DNI', 'CV', 'Constancia'] as $tipo) {
                    App\Models\Documento::create([
                        'postulante_id' => $postulante->id,
                        'tipo' => $tipo,
                        'nombre_archivo' => 'test_' . strtolower($tipo) . '.pdf',
                        'url' => 'http://example.com/test.pdf',
                        'estado' => 1
                    ]);
                }
                
                // Registro específico para la FOTO (JPG real)
                App\Models\Documento::create([
                    'postulante_id' => $postulante->id,
                    'tipo' => 'Foto',
                    'nombre_archivo' => 'fotos/test_foto.jpg', // Ruta relativa como lo espera el storage
                    'url' => 'http://localhost:8000/storage/fotos/test_foto.jpg',
                    'estado' => 1
                ]);

                echo 'Inscripción creada con éxito para ' . $dni;
            } catch (\Exception $e) {
                echo 'Error en createTestInscripcion: ' . $e->getMessage();
            }
            