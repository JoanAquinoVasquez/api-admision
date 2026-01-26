<?php

namespace Database\Seeders;

use App\Models\Docente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Docente::create([
            'nombres' => 'Juan',
            'ap_paterno' => 'Perez',
            'ap_materno' => 'Gomez',
            'dni' => '12345678',
            'email' => 'docente1@gmail.com',
            'password' => bcrypt('12345678')
        ]);

        // Docente::create([
        //     'nombres' => 'Maria',
        //     'ap_paterno' => 'Rojas',
        //     'ap_materno' => 'Sanchez',
        //     'dni' => '87654321',
        //     'email' => 'docente2@gmail.com',
        //     'password' => bcrypt('12345678')
        // ]);

        // Docente::create([
        //     'nombres' => 'Carlos',
        //     'ap_paterno' => 'Garcia',
        //     'ap_materno' => 'Gonzales',
        //     'dni' => '12348765',
        //     'email' => 'docente3@gmail.com',
        //     'password' => bcrypt('12345678')
        // ]);

        // Docente::create([
        //     'nombres' => 'Luis',
        //     'ap_paterno' => 'Gonzales',
        //     'ap_materno' => 'Vasquez',
        //     'dni' => '87651234',
        //     'email' => 'docente4@gmail.com',
        //     'password' => bcrypt('12345678')
        // ]);
    }
}
