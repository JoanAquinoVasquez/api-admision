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

        // 1. Exact match (case insensitive)
        foreach ($programas as $p) {
            if ($this->matchClean($p->nombre) == $cleanName)
                return $p;
        }

        // 2. Special cases for common ambiguous words
        if ($cleanName === 'derecho') {
            return $programas->first(function ($p) {
                $n = $this->matchClean($p->nombre);
                return str_contains($n, 'derecho') && !str_contains($n, 'penal') && !str_contains($n, 'civil');
            }) ?? $programas->first(function ($p) {
                return str_contains($this->matchClean($p->nombre), 'derecho');
            });
        }

        // 3. Keyword/Mapping direct checks
        $mappings = [
            'hidraulica' => 'Ciencias con mención en Ingeniería Hidráulica',
            'obras y construccion' => 'Gerencia de Obras y Construcción',
            'penal' => 'Derecho con mención en Derecho Penal y Procesal Penal',
            'civil' => 'Derecho con mención en Civil y Comercial',
            'constitucional' => 'Derecho con mención en Derecho Constitucional y Procesal Constitucional',
            'sistemas' => 'Ingeniería de Sistemas con Mención en Gerencia de Tecnologías de la Información y Gestión del Software',
            'suelos' => 'Manejo Sostenible de Suelos',
            'plagas' => 'Ciencias con mención en Manejo Integrado de Plagas y Enfermedades',
            'ambiental' => 'Gestión Ambiental - PRESENCIAL',
            'calidad' => 'Ciencias con mención en Gestión de la Calidad e Inocuidad de Alimentos',
            'procesos' => 'Ciencias con mención en Ingeniería de Procesos Industriales',
            'proyectos' => 'Ciencias con mención en Proyectos de Inversión',
            'gerencia empresarial' => 'Administración con mención en Gerencia Empresarial',
            'administracion' => 'Administración con mención en Gerencia Empresarial',
            'investigacion' => 'Ciencias de la Educación con mención en Investigación y Docencia',
            'veterinaria' => 'Ciencias Veterinarias con Mención en Salud Animal',
            'arquitecto' => 'Territorio y Urbanismo Sostenible',
            'urbano' => 'Ordenamiento Territorial y Desarrollo Urbano',
            'recursos hidricos' => 'Gestión Integrada de los Recursos Hídricos',
            'obras' => 'Gerencia de Obras y Construcción',
        ];

        foreach ($mappings as $key => $targetName) {
            $cleanKey = $this->matchClean($key);
            if (str_contains($cleanName, $cleanKey)) {
                $cleanTarget = $this->matchClean($targetName);
                $found = $programas->first(function ($p) use ($cleanTarget) {
                    return str_contains($this->matchClean($p->nombre), $cleanTarget);
                });
                if ($found)
                    return $found;
            }
        }

        // 4. Broad keyword match (if name is long enough)
        if (strlen($cleanName) > 5) {
            foreach ($programas as $p) {
                $pClean = $this->matchClean($p->nombre);
                if (str_contains($pClean, $cleanName))
                    return $p;
            }
        }

        // 5. Fallback: Levenshtein distance on cleaned names
        $bestMatch = null;
        $shortest = -1;
        foreach ($programas as $p) {
            $pClean = $this->matchClean($p->nombre);
            $pCleanShort = str_replace(['ciencias con mencion en ', 'maestria en ', 'doctorado en ', ' con mencion en '], '', $pClean);

            $lev = levenshtein($cleanName, $pCleanShort);
            if ($lev < $shortest || $shortest < 0) {
                $bestMatch = $p;
                $shortest = $lev;
            }
        }

        // Threshold: distance must be less than 50% of the input length
        if ($shortest >= 0 && $shortest < strlen($cleanName) * 0.5) {
            return $bestMatch;
        }

        return null;
    }
}
