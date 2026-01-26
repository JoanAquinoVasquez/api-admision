<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\Postulante;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crear una instancia de Faker
        $faker = Faker::create();

        // Obtener todos los postulantes
        $postulantes = Postulante::all();

        foreach ($postulantes as $postulante) {
            // Crear los 4 documentos para cada postulante
            Documento::factory()->create([
                'postulante_id' => $postulante->id,
                'tipo'          => 'Curriculum',
                'url'           => $faker->url,
                'nombre_archivo' => 'cv.pdf',
            ]);

            Documento::factory()->create([
                'postulante_id' => $postulante->id,
                'tipo'          => 'Foto',
                'url'           => $faker->url,
                'nombre_archivo' => 'foto.jpeg',
            ]);

            Documento::factory()->create([
                'postulante_id' => $postulante->id,
                'tipo'          => 'DocumentoIdentidad',
                'url'           => $faker->url,
                'nombre_archivo' => 'documentoIdentidad.pdf',
            ]);

            Documento::factory()->create([
                'postulante_id' => $postulante->id,
                'tipo'          => 'Voucher',
                'url'           => $faker->url,
                'nombre_archivo' => 'fotoVoucher.pdf',
            ]);
        }
    }
}
