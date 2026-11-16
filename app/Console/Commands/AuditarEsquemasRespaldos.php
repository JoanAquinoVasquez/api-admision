<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class AuditarEsquemasRespaldos extends Command
{
    protected $signature = 'admision:auditar-esquemas-respaldos';

    protected $description = 'Compara las estructuras de tablas de los respaldos SQL sin conectarse a bases de datos.';

    private const FUENTES = [
        '2024-II' => 'epgunpr689_inscripcion_epg.sql',
        '2025-I' => 'epgunpr689_db_admision2025I.sql',
        '2026-I' => 'epgunpr689_db_admision2026I.sql',
    ];

    public function handle(): int
    {
        $directory = base_path('../bds');
        $schemas = [];

        foreach (self::FUENTES as $periodo => $fileName) {
            $path = $directory . DIRECTORY_SEPARATOR . $fileName;
            $sql = is_file($path) ? file_get_contents($path) : false;
            if ($sql === false) {
                $this->error("No se pudo leer el respaldo de {$periodo}: {$path}");
                return self::FAILURE;
            }

            $schemas[$periodo] = $this->extractSchemas($sql);
            if ($schemas[$periodo] === []) {
                throw new RuntimeException("No se reconocieron instrucciones CREATE TABLE en {$fileName}.");
            }
        }

        $allTables = array_unique(array_merge(...array_values(array_map('array_keys', $schemas))));
        sort($allTables);

        $counts = [];
        foreach ($schemas as $periodo => $schema) {
            $counts[] = [$periodo, count($schema)];
        }
        $this->table(['Respaldo', 'Tablas'], $counts);

        $differences = [];
        foreach ($allTables as $table) {
            $byPeriod = [];
            foreach ($schemas as $periodo => $schema) {
                $byPeriod[$periodo] = $schema[$table] ?? null;
            }

            $available = array_filter($byPeriod, static fn ($columns) => $columns !== null);
            $variants = array_unique(array_map(static fn ($columns) => implode(',', $columns), $available));
            if (count($available) !== count($schemas) || count($variants) > 1) {
                $differences[] = [
                    $table,
                    $this->describe($byPeriod['2024-II']),
                    $this->describe($byPeriod['2025-I']),
                    $this->describe($byPeriod['2026-I']),
                ];
            }
        }

        $this->newLine();
        $this->comment('Tablas ausentes o con columnas distintas:');
        $this->table(['Tabla', '2024-II', '2025-I', '2026-I'], $differences);
        $this->comment('Auditoría local de archivos: no se conectó ni escribió en ninguna base de datos.');

        return self::SUCCESS;
    }

    /** @return array<string, list<string>> */
    private function extractSchemas(string $sql): array
    {
        $schemas = [];
        preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/is', $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $columnMatches);
            $schemas[strtolower($match[1])] = array_map('strtolower', $columnMatches[1]);
        }

        return $schemas;
    }

    /** @param list<string>|null $columns */
    private function describe(?array $columns): string
    {
        if ($columns === null) {
            return '—';
        }

        return count($columns) . ' cols: ' . implode(', ', $columns);
    }
}
