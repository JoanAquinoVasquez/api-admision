<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class AuditarRespaldosAdmision extends Command
{
    protected $signature = 'admision:auditar-respaldos';

    protected $description = 'Audita conteos y consistencia básica de los respaldos SQL sin modificar bases de datos.';

    private const FUENTES = [
        '2024-II' => 'epgunpr689_inscripcion_epg.sql',
        '2025-I' => 'epgunpr689_db_admision2025I.sql',
        '2026-I' => 'epgunpr689_db_admision2026I.sql',
    ];

    private const TABLAS_RELEVANTES = [
        'postulantes',
        'pre_inscripcions',
        'programas',
        'inscripcions',
        'vouchers',
        'tabla_voucher',
        'documentos',
        'notas',
    ];

    public function handle(): int
    {
        $directory = base_path('../bds');
        $sources = [];

        foreach (self::FUENTES as $periodo => $fileName) {
            $path = $directory . DIRECTORY_SEPARATOR . $fileName;
            if (! is_file($path)) {
                $this->error("No se encontró el respaldo esperado para {$periodo}: {$path}");
                return self::FAILURE;
            }

            try {
                $sources[$periodo] = $this->auditFile($path);
            } catch (RuntimeException $exception) {
                $this->error("{$periodo}: {$exception->getMessage()}");
                return self::FAILURE;
            }
        }

        $rows = [];
        foreach ($sources as $periodo => $source) {
            $counts = $source['counts'];
            $rows[] = [
                $periodo,
                $counts['postulantes'] ?? 0,
                $counts['pre_inscripcions'] ?? 0,
                $counts['programas'] ?? 0,
                $counts['inscripcions'] ?? 0,
                $counts['vouchers'] ?? $counts['tabla_voucher'] ?? 0,
                $counts['documentos'] ?? 0,
                $counts['notas'] ?? 0,
                $source['preinscripciones_sin_postulante'],
            ];
        }

        $this->table(
            ['Periodo', 'Personas', 'Preinscripciones', 'Programas', 'Inscripciones', 'Vouchers', 'Documentos', 'Notas', 'Preinsc. sin persona'],
            $rows
        );

        $allDnis = [];
        $allPreinscripcionDnis = [];
        foreach ($sources as $periodo => $source) {
            $dnis = $source['dnis_postulantes'];
            $allDnis = array_merge($allDnis, array_keys($dnis));
            $allPreinscripcionDnis = array_merge($allPreinscripcionDnis, array_keys($source['dnis_preinscripciones']));
            $this->line(sprintf(
                '%s: %d DNI distintos en postulantes, %d duplicados dentro del periodo.',
                $periodo,
                count($dnis),
                $source['dni_duplicados_postulantes']
            ));
        }

        $this->info(sprintf(
            'Personas distintas al unificar por DNI: %d (frente a %d filas de postulantes sumadas).',
            count(array_unique($allDnis)),
            count($allDnis)
        ));
        $globalPostulanteSet = array_fill_keys(array_unique($allDnis), true);
        $globalPreinscripcionSet = array_fill_keys(array_unique($allPreinscripcionDnis), true);
        $this->line(sprintf(
            'DNI de preinscripciones sin persona en ningún periodo: %d.',
            count(array_diff_key($globalPreinscripcionSet, $globalPostulanteSet))
        ));

        $legacy = $sources['2024-II'];
        $this->line(sprintf(
            '2024-II: %d de %d inscripciones enlazan con un voucher por nro_voucher; vouchers fuera de los límites actuales: %d.',
            $legacy['vouchers_2024_enlazados'],
            $legacy['counts']['inscripcions'] ?? 0,
            $legacy['vouchers_2024_fuera_de_limite']
        ));
        $this->comment('Auditoría de solo lectura: no conecta ni escribe en ninguna base de datos.');

        return self::SUCCESS;
    }

    /** @return array{counts: array<string, int>, dnis_postulantes: array<string, true>, dnis_preinscripciones: array<string, true>, dni_duplicados_postulantes: int, preinscripciones_sin_postulante: int} */
    private function auditFile(string $path): array
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer el archivo.');
        }

        $schemas = $this->extractSchemas($sql);
        $counts = [];
        $postulanteDnis = [];
        $preinscripcionDnis = [];
        $dataRows = [];

        if (! preg_match_all('/INSERT\s+INTO\s+`([^`]+)`/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('El respaldo no contiene instrucciones INSERT reconocibles.');
        }

        foreach ($matches[0] as $index => [$insertToken, $insertOffset]) {
            $table = strtolower($matches[1][$index][0]);
            if (! in_array($table, self::TABLAS_RELEVANTES, true)) {
                continue;
            }

            $afterTable = $insertOffset + strlen($insertToken);
            $valuesOffset = stripos($sql, 'VALUES', $afterTable);
            if ($valuesOffset === false) {
                continue;
            }

            $header = substr($sql, $afterTable, $valuesOffset - $afterTable);
            $columns = $this->extractInsertColumns($header) ?: ($schemas[$table] ?? []);
            $valuesStart = $valuesOffset + strlen('VALUES');
            $statement = $this->readValuesStatement($sql, $valuesStart);
            $counts[$table] = ($counts[$table] ?? 0) + $statement['rows'];

            $needsRows = in_array($table, ['postulantes', 'pre_inscripcions', 'inscripcions', 'tabla_voucher'], true);
            $statementRows = [];
            if ($needsRows) {
                foreach ($statement['tuples'] as $tuple) {
                    $values = array_map($this->decodeSqlValue(...), $this->splitTupleFields($tuple));
                    if (count($values) === count($columns)) {
                        $statementRows[] = array_combine($columns, $values);
                    }
                }
                $dataRows[$table] = array_merge($dataRows[$table] ?? [], $statementRows);
            }

            $dniColumn = $table === 'postulantes' ? $this->findColumn($columns, ['num_iden', 'dni']) : null;
            if ($dniColumn === null && $table === 'pre_inscripcions') {
                $dniColumn = $this->findColumn($columns, ['num_iden', 'dni']);
            }

            if ($dniColumn === null || ! in_array($table, ['postulantes', 'pre_inscripcions'], true)) {
                continue;
            }

            $dniColumnName = $columns[$dniColumn];
            foreach ($statementRows as $row) {
                $dni = $this->normalizeDni((string) ($row[$dniColumnName] ?? ''));
                if ($dni !== null) {
                    if ($table === 'postulantes') {
                        $postulanteDnis[] = $dni;
                    } else {
                        $preinscripcionDnis[] = $dni;
                    }
                }
            }
        }

        $postulanteSet = array_fill_keys($postulanteDnis, true);
        $preinscripcionSet = array_fill_keys($preinscripcionDnis, true);
        $duplicates = count($postulanteDnis) - count($postulanteSet);
        $missingPostulante = count(array_diff_key($preinscripcionSet, $postulanteSet));
        [$legacyVoucherLinks, $legacyVoucherOverflow] = $this->auditLegacyVouchers($dataRows);

        return [
            'counts' => $counts,
            'dnis_postulantes' => $postulanteSet,
            'dnis_preinscripciones' => $preinscripcionSet,
            'dni_duplicados_postulantes' => $duplicates,
            'preinscripciones_sin_postulante' => $missingPostulante,
            'vouchers_2024_enlazados' => $legacyVoucherLinks,
            'vouchers_2024_fuera_de_limite' => $legacyVoucherOverflow,
        ];
    }

    /** @param array<string, list<array<string, string|null>>> $dataRows
     *  @return array{int, int}
     */
    private function auditLegacyVouchers(array $dataRows): array
    {
        $voucherNumbers = [];
        foreach ($dataRows['tabla_voucher'] ?? [] as $voucher) {
            $number = trim((string) ($voucher['nro_voucher'] ?? ''));
            if ($number !== '') {
                $voucherNumbers[$number] = true;
            }
        }

        $linked = 0;
        foreach ($dataRows['inscripcions'] ?? [] as $inscription) {
            $number = trim((string) ($inscription['cod_voucher'] ?? ''));
            if ($number !== '' && isset($voucherNumbers[$number])) {
                $linked++;
            }
        }

        $overflow = 0;
        foreach ($dataRows['tabla_voucher'] ?? [] as $voucher) {
            if (strlen((string) ($voucher['nro_voucher'] ?? '')) > 7
                || strlen((string) ($voucher['dni'] ?? '')) > 20
                || strlen((string) ($voucher['nombre_completo'] ?? '')) > 150
                || strlen((string) ($voucher['cajero'] ?? '')) > 4
                || strlen((string) ($voucher['agencia'] ?? '')) > 4
                || abs((float) ($voucher['monto'] ?? 0)) > 9999.99) {
                $overflow++;
            }
        }

        return [$linked, $overflow];
    }

    private function decodeSqlValue(string $value): ?string
    {
        $value = trim($value);
        if (strtoupper($value) === 'NULL') {
            return null;
        }

        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
            $value = str_replace(
                ["\\0", "\\b", "\\n", "\\r", "\\t", "\\Z", "\\'", '\\"', "''", '\\\\'],
                ["\0", "\b", "\n", "\r", "\t", "\x1a", "'", '"', "'", '\\'],
                $value
            );
        }

        return $value;
    }

    /** @return array<string, list<string>> */
    private function extractSchemas(string $sql): array
    {
        $schemas = [];
        if (! preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/is', $sql, $matches, PREG_SET_ORDER)) {
            return $schemas;
        }

        foreach ($matches as $match) {
            preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $columns);
            $schemas[strtolower($match[1])] = array_map('strtolower', $columns[1]);
        }

        return $schemas;
    }

    /** @return list<string> */
    private function extractInsertColumns(string $header): array
    {
        if (! preg_match('/\((.*?)\)\s*$/s', trim($header), $match)) {
            return [];
        }

        preg_match_all('/`([^`]+)`/', $match[1], $columns);
        return array_map('strtolower', $columns[1]);
    }

    /** @return array{rows: int, tuples: list<string>} */
    private function readValuesStatement(string $sql, int $offset): array
    {
        $length = strlen($sql);
        $depth = 0;
        $quote = null;
        $escaped = false;
        $tupleStart = null;
        $tuples = [];

        for ($i = $offset; $i < $length; $i++) {
            $char = $sql[$i];
            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    if ($i + 1 < $length && $sql[$i + 1] === $quote) {
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if (in_array($char, ["'", '"', '`'], true)) {
                $quote = $char;
            } elseif ($char === '(') {
                if ($depth === 0) {
                    $tupleStart = $i + 1;
                }
                $depth++;
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0 && $tupleStart !== null) {
                    $tuples[] = substr($sql, $tupleStart, $i - $tupleStart);
                    $tupleStart = null;
                }
            } elseif ($char === ';' && $depth === 0) {
                break;
            }
        }

        return ['rows' => count($tuples), 'tuples' => $tuples];
    }

    /** @return list<string> */
    private function splitTupleFields(string $tuple): array
    {
        $fields = [];
        $start = 0;
        $depth = 0;
        $quote = null;
        $escaped = false;
        $length = strlen($tuple);

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];
            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    if ($i + 1 < $length && $tuple[$i + 1] === $quote) {
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if (in_array($char, ["'", '"', '`'], true)) {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $fields[] = trim(substr($tuple, $start, $i - $start));
                $start = $i + 1;
            }
        }

        $fields[] = trim(substr($tuple, $start));
        return $fields;
    }

    /** @param list<string> $columns */
    private function findColumn(array $columns, array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $index = array_search($candidate, $columns, true);
            if ($index !== false) {
                return $index;
            }
        }

        return null;
    }

    private function normalizeDni(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
            $value = str_replace(["\\'", "''", '\\\\'], ["'", "'", '\\'], $value);
        }

        $value = mb_strtoupper(trim($value), 'UTF-8');
        return $value === '' ? null : $value;
    }
}
