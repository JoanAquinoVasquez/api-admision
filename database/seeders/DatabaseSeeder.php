<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Documento;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DepartamentoSeeder::class,
            ProvinciaSeeder::class,
            DistritoSeeder::class,
            GradoSeeder::class,
            FacultadSeeder::class,
            ConceptoPagoSeeder::class,
            ProgramaSeeder::class,
            // PreInscripcionSeeder::class,
            // VoucherSeeder::class,
            //PostulanteSeeder::class,
            // InscripcionSeeder::class,
            DocenteSeeder::class,
            // ComisionAdmisionSeeder::class,
            // DocumentoSeeder::class,
            // NotaSeeder::class,
            //RoleSeeder::class,
        ]);
    }
}
