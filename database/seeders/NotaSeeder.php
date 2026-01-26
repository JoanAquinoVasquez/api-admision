<?php

namespace Database\Seeders;

use App\Models\Nota;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Nota::factory()->count(10)->create();
    }
}
