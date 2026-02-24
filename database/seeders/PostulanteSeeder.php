<?php

namespace Database\Seeders;

use App\Models\Postulante;
use Illuminate\Database\Seeder;

class PostulanteSeeder extends Seeder
{
    public function run()
    {
        Postulante::factory(1000)->create();
    }
}
