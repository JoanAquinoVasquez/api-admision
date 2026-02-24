<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PreInscripcion;
use App\Models\Programa;
use App\Models\Distrito;
use Illuminate\Support\Str;

class ImportPreinscripciones extends Command
{
    protected $signature = 'excel:import-preinscripciones {file}';
    protected $description = 'Import preinscripciones from Excel file';

    public function handle()
    {
        $file = $this->argument('file');
        $path = base_path($file);

        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $this->info("Reading Excel file...");
        $data = Excel::toArray(new \stdClass(), $path);

        if (empty($data) || empty($data[0])) {
            $this->error("No data found in Excel");
            return;
        }

        $rows = $data[0];
        $header = array_shift($rows); // Remove header row

        $programas = Programa::all();
        $defaultDistritoId = 1; // Default to first district if unknown

        $count = 0;
        $errors = 0;
        $unmatchedPrograms = [];

        foreach ($rows as $index => $row) {
            if (empty($row[0]))
                continue; // Skip empty rows

            $fullName = trim($row[0]);
            $dni = trim($row[1] ?? '');
            $programaExcel = trim($row[3] ?? '');
            $telefono = trim($row[4] ?? '');
            $email = trim($row[5] ?? '');

            if (empty($dni) || empty($email)) {
                $this->warn("Fila " . ($index + 2) . ": DNI o Correo vacío. Omitiendo.");
                $errors++;
                continue;
            }

            // Match Program
            $matchedPrograma = $this->matchPrograma($programaExcel, $programas);

            if (!$matchedPrograma) {
                if (!empty($programaExcel)) {
                    $unmatchedPrograms[] = $programaExcel;
                }
                $this->warn("Row " . ($index + 2) . ": Could not match program '$programaExcel'. Skipping.");
                $errors++;
                continue;
            }

            // Simple splitting of names
            $nameParts = explode(' ', $fullName);
            $countParts = count($nameParts);

            $nombres = "";
            $apPaterno = "";
            $apMaterno = "";

            if ($countParts >= 3) {
                $apMaterno = array_pop($nameParts);
                $apPaterno = array_pop($nameParts);
                $nombres = implode(' ', $nameParts);
            } elseif ($countParts == 2) {
                $nombres = $nameParts[0];
                $apPaterno = $nameParts[1];
                $apMaterno = "S/M";
            } else {
                $nombres = $fullName;
                $apPaterno = "S/P";
                $apMaterno = "S/M";
            }

            try {
                // Check if already exists by DNI to avoid duplicates if re-running
                if (!empty($dni) && PreInscripcion::where('num_iden', $dni)->exists()) {
                    $this->line("Row " . ($index + 2) . ": DNI $dni already exists. Skipping.");
                    continue;
                }

                PreInscripcion::create([
                    'nombres' => strtoupper($nombres),
                    'ap_paterno' => strtoupper($apPaterno),
                    'ap_materno' => strtoupper($apMaterno),
                    'num_iden' => $dni,
                    'tipo_doc' => 'DNI',
                    'email' => $email,
                    'celular' => $telefono,
                    'programa_id' => $matchedPrograma->id,
                    'distrito_id' => $defaultDistritoId,
                    'fecha_nacimiento' => '1900-01-01',
                    'sexo' => 'M', // Default
                    'estado' => true,
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->error("Row " . ($index + 2) . ": Error saving record. " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Import completed! $count records added, $errors errors/skips.");

        if (!empty($unmatchedPrograms)) {
            $this->warn("\nUnmatched Program Names encountered:");
            foreach (array_unique($unmatchedPrograms) as $prog) {
                $this->line("- $prog");
            }
        }
    }

    private function matchClean($name)
    {
        return Str::slug($name, ' ');
    }

    private function matchPrograma($name, $programas)
    {
        if (empty($name))
            return null;

        $name = strtolower(trim($name));
        $cleanName = $this->matchClean($name);

        // Detect degree type
        $gradoId = null;
        if (str_contains($cleanName, 'doctorado') || str_contains($cleanName, 'doctor')) {
            $gradoId = 1;
        } elseif (str_contains($cleanName, 'maestria')) {
            $gradoId = 2;
        } elseif (str_contains($cleanName, 'especialidad')) {
            $gradoId = 3;
        }

        // Filter programs by degree if detected
        $filteredProgramas = $programas;
        if ($gradoId) {
            $filteredProgramas = $programas->where('grado_id', $gradoId);
            if ($filteredProgramas->isEmpty()) {
                $filteredProgramas = $programas;
            }
        }

        // 1. Exact match (case insensitive)
        foreach ($filteredProgramas as $p) {
            if ($this->matchClean($p->nombre) == $cleanName)
                return $p;
        }

        // 2. Keyword/Mapping direct checks
        $mappings = [
            'hidraulica' => 'Ingeniería Hidráulica',
            'obras y construccion' => 'Gerencia de Obras y Construcción',
            'penal' => 'Derecho Penal',
            'civil' => 'Derecho con mención en Civil y Comercial',
            'constitucional' => 'Derecho Constitucional',
            'sistemas' => 'Ingeniería de Sistemas',
            'suelos' => 'Manejo Sostenible de Suelos',
            'suelo' => 'Manejo Sostenible de Suelos',
            'plagas' => 'Manejo Integrado de Plagas',
            'ambiental' => 'Gestión Ambiental',
            'calidad' => 'Gestión de la Calidad',
            'procesos' => 'Ingeniería de Procesos Industriales',
            'proyectos' => 'Proyectos de Inversión',
            'gerencia empresarial' => 'Gerencia Empresarial',
            'gestion universitaria' => 'Docencia y Gestión Universitaria',
            'docencia universitaria' => 'Docencia y Gestión Universitaria',
            'educacion universitaria' => 'Docencia y Gestión Universitaria',
            'gerencia educativa' => 'Gerencia Educativa Estratégica',
            'gestion publica' => 'Gestión Pública y Gerencia Social',
            'investigacion y docencia' => 'Investigación y Docencia',
            'docencia e investigacion' => 'Investigación y Docencia',
            'mencion en investigacion' => 'Investigación y Docencia',
            'veterinaria' => 'Ciencias Veterinarias',
            'arquitecto' => 'Territorio y Urbanismo',
            'urbano' => 'Ordenamiento Territorial',
            'recursos hidricos' => 'Recursos Hídricos',
            'enfermeria' => 'Ciencias de Enfermería',
            'agroexportacion' => 'Agroexportación Sostenible',
            'gerenica' => 'Gerencia de Obras',
            'obras' => 'Gerencia de Obras',
        ];

        foreach ($mappings as $key => $targetName) {
            $cleanKey = $this->matchClean($key);
            if (str_contains($cleanName, $cleanKey)) {
                $cleanTarget = $this->matchClean($targetName);
                $found = $filteredProgramas->first(function ($p) use ($cleanTarget) {
                    return str_contains($this->matchClean($p->nombre), $cleanTarget);
                });
                if ($found)
                    return $found;
            }
        }

        // 4. Broad keyword match (only if unique)
        if (strlen($cleanName) > 5) {
            $matches = $filteredProgramas->filter(function ($p) use ($cleanName) {
                $pClean = $this->matchClean($p->nombre);
                return str_contains($pClean, $cleanName) || str_contains($cleanName, $pClean);
            });
            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        return null;
    }
}
