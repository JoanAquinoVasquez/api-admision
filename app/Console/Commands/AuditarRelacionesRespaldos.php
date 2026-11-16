<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class AuditarRelacionesRespaldos extends Command
{
    protected $signature = 'admision:auditar-relaciones-respaldos';

    protected $description = 'Revisa referencias y posibles cruces de catálogo en respaldos SQL sin modificar datos.';

    private const FUENTES = [
        '2024-II' => 'epgunpr689_inscripcion_epg.sql',
        '2025-I' => 'epgunpr689_db_admision2025I.sql',
        '2026-I' => 'epgunpr689_db_admision2026I.sql',
    ];

    private const TABLAS = [
        'departamentos', 'provincias', 'distritos', 'grados', 'facultads',
        'concepto_pagos', 'programas', 'postulantes', 'pre_inscripcions',
        'inscripcions', 'vouchers', 'tabla_voucher', 'documentos', 'notas',
        'correos', 'docentes', 'comision_admisions',
    ];

    public function handle(): int
    {
        $sources = [];
        foreach (self::FUENTES as $periodo => $fileName) {
            $path = base_path('../bds/' . $fileName);
            $sql = is_file($path) ? file_get_contents($path) : false;
            if ($sql === false) {
                $this->error("No se pudo leer el respaldo {$periodo}.");
                return self::FAILURE;
            }

            $sources[$periodo] = $this->parse($sql);
        }

        $this->info('Referencias incompletas por periodo (las que no se usan en ese esquema se omiten):');
        $referenceRows = [];
        foreach ($sources as $periodo => $data) {
            $referenceRows[] = [$periodo, ...$this->auditReferences($data)];
        }
        $this->table(
            ['Periodo', 'Programa sin grado/facultad', 'Preinsc. sin programa/distrito', 'Inscr. sin persona/programa/voucher', 'Voucher legacy sin enlace', 'Doc. sin persona', 'Nota sin inscripción'],
            $referenceRows
        );

        $programIdentities = [];
        $programConflicts = [];
        $legacyPaymentCodes = [];
        $paymentConceptCodes = [];
        $legacyGeo = [];
        $legacyGeoMissing = 0;
        $legacyFiles = [];
        $legacyVoucherNumberDuplicates = 0;
        $conceptCodeVariants = [];
        $statusRows = [];
        foreach ($sources as $periodo => $data) {
            $programRows = $data['programas'] ?? [];
            $grades = $this->lookupById($data['grados'] ?? []);
            $faculties = $this->lookupById($data['facultads'] ?? []);
            $withinPeriod = [];

            foreach ($programRows as $program) {
                $grade = $grades[(string) ($program['grado_id'] ?? '')]['nombre'] ?? '';
                $faculty = $faculties[(string) ($program['facultad_id'] ?? '')]['nombre'] ?? '';
                $identity = $this->normalize((string) ($program['nombre'] ?? '')) . '|'
                    . $this->normalize((string) $grade) . '|'
                    . $this->normalize((string) $faculty);
                if (trim($identity, '|') === '') {
                    continue;
                }

                $programIdentities[$identity][$periodo] = true;
                $withinPeriod[$identity][] = $program['id'] ?? null;
            }

            foreach ($withinPeriod as $identity => $ids) {
                if (count($ids) > 1) {
                    $programConflicts[] = [$periodo, $identity, count($ids)];
                }
            }

            if ($periodo === '2024-II') {
                $voucherNumberCounts = [];
                foreach ($data['tabla_voucher'] ?? [] as $voucher) {
                    $code = trim((string) ($voucher['cod_pago'] ?? ''));
                    if ($code !== '') {
                        $legacyPaymentCodes[$code] = true;
                    }
                    $number = trim((string) ($voucher['nro_voucher'] ?? ''));
                    if ($number !== '') {
                        $voucherNumberCounts[$number] = ($voucherNumberCounts[$number] ?? 0) + 1;
                    }
                }
                $legacyVoucherNumberDuplicates = count(array_filter($voucherNumberCounts, static fn ($count) => $count > 1));

                foreach (['rutadni', 'foto', 'cv'] as $column) {
                    $legacyFiles[$column] = count(array_filter(
                        $data['postulantes'] ?? [],
                        static fn ($person) => trim((string) ($person[$column] ?? '')) !== ''
                    ));
                }
                $legacyFiles['voucher'] = count(array_filter(
                    $data['inscripcions'] ?? [],
                    static fn ($registration) => trim((string) ($registration['voucher'] ?? '')) !== ''
                ));
                foreach ($data['postulantes'] ?? [] as $person) {
                    $department = trim((string) ($person['departamento'] ?? ''));
                    $province = trim((string) ($person['provincia'] ?? ''));
                    $district = trim((string) ($person['distrito'] ?? ''));
                    if ($department === '' || $province === '' || $district === '') {
                        $legacyGeoMissing++;
                    }
                    $legacyGeo[implode('|', array_map($this->normalize(...), [$department, $province, $district]))] = true;
                }
            }

            foreach ($data['concepto_pagos'] ?? [] as $concept) {
                $code = trim((string) ($concept['cod_concepto'] ?? ''));
                if ($code !== '') {
                    $paymentConceptCodes[$periodo][$code] = true;
                    $conceptCodeVariants[$periodo][$code][implode('|', [
                        trim((string) ($concept['nombre'] ?? '')),
                        trim((string) ($concept['monto'] ?? '')),
                    ])] = true;
                }
            }

            foreach (['estado', 'validado', 'expediente', 'val_digital', 'val_fisico'] as $column) {
                $distribution = $this->distribution($data['inscripcions'] ?? [], $column);
                if ($distribution !== []) {
                    $statusRows[] = [$periodo, "inscripcions.{$column}", $this->formatDistribution($distribution)];
                }
            }
            $voucherTable = $periodo === '2024-II' ? 'tabla_voucher' : 'vouchers';
            $voucherStates = $this->distribution($data[$voucherTable] ?? [], 'estado');
            if ($voucherStates !== []) {
                $statusRows[] = [$periodo, "{$voucherTable}.estado", $this->formatDistribution($voucherStates)];
            }
        }

        $overlaps = 0;
        $presenceCounts = [];
        foreach ($programIdentities as $periods) {
            $presence = implode('+', array_keys($periods));
            $presenceCounts[$presence] = ($presenceCounts[$presence] ?? 0) + 1;
            if (count($periods) > 1) {
                $overlaps++;
            }
        }

        $this->newLine();
        $this->table(
            ['Revisión de mapeo', 'Resultado'],
            [
                ['Programas distintos por nombre + grado + facultad (coincidencia normalizada)', count($programIdentities)],
                ['Identidades presentes en más de un periodo (candidatas a compartir catálogo)', $overlaps],
                ['Duplicados de identidad dentro de un mismo respaldo (requieren revisión)', count($programConflicts)],
                ['Códigos de pago distintos en vouchers 2024-II', count($legacyPaymentCodes)],
                ['Combinaciones geográficas distintas en postulantes 2024-II', count($legacyGeo)],
                ['Postulantes 2024-II con algún dato geográfico vacío', $legacyGeoMissing],
                ['Números de voucher 2024-II repetidos en origen', $legacyVoucherNumberDuplicates],
            ]
        );

        $this->newLine();
        $tableCounts = [];
        foreach ($sources as $periodo => $data) {
            foreach (self::TABLAS as $table) {
                if (isset($data[$table])) {
                    $tableCounts[] = [$periodo, $table, count($data[$table])];
                }
            }
        }
        $this->table(['Periodo', 'Tabla', 'Filas a revisar/importar'], $tableCounts);

        if ($legacyFiles !== []) {
            $this->newLine();
            $this->table(
                ['Archivos heredados 2024-II', 'Filas con ruta'],
                array_map(static fn ($type, $count) => [$type, $count], array_keys($legacyFiles), array_values($legacyFiles))
            );
        }

        $this->newLine();
        $this->table(
            ['Programas por presencia', 'Cantidad'],
            [
                ['Solo 2024-II', $presenceCounts['2024-II'] ?? 0],
                ['Solo 2025-I', $presenceCounts['2025-I'] ?? 0],
                ['Solo 2026-I', $presenceCounts['2026-I'] ?? 0],
                ['2024-II y 2025-I', $presenceCounts['2024-II+2025-I'] ?? 0],
                ['2024-II y 2026-I', $presenceCounts['2024-II+2026-I'] ?? 0],
                ['2025-I y 2026-I', $presenceCounts['2025-I+2026-I'] ?? 0],
                ['En los tres periodos', $presenceCounts['2024-II+2025-I+2026-I'] ?? 0],
            ]
        );

        if ($legacyPaymentCodes !== []) {
            $this->line('Códigos cod_pago 2024-II: ' . implode(', ', array_keys($legacyPaymentCodes)));
            $knownConceptCodes = array_fill_keys(array_keys($paymentConceptCodes['2025-I'] ?? []), true)
                + array_fill_keys(array_keys($paymentConceptCodes['2026-I'] ?? []), true);
            $matchedCodes = array_intersect_key($legacyPaymentCodes, $knownConceptCodes);
            $unknownCodes = array_diff_key($legacyPaymentCodes, $knownConceptCodes);
            $this->line('Coincidencias exactas con cod_concepto de 2025-I/2026-I: ' . count($matchedCodes));
            if ($unknownCodes !== []) {
                $this->comment('Códigos 2024-II sin coincidencia exacta de concepto: ' . implode(', ', array_keys($unknownCodes)));
            }
        }

        $ambiguousConceptCodes = [];
        foreach ($conceptCodeVariants as $periodo => $codes) {
            foreach ($codes as $code => $variants) {
                if (count($variants) > 1) {
                    $ambiguousConceptCodes[] = [$periodo, $code, count($variants)];
                }
            }
        }
        if ($ambiguousConceptCodes !== []) {
            $this->newLine();
            $this->warn('Códigos de concepto repetidos con nombre o monto distintos:');
            $this->table(['Periodo', 'Código', 'Variantes'], $ambiguousConceptCodes);
        }
        if ($statusRows !== []) {
            $this->newLine();
            $this->table(['Periodo', 'Campo', 'Valores y conteos'], $statusRows);
        }

        if ($programConflicts !== []) {
            $this->comment('Se omiten nombres en la salida; ejecutar una revisión manual antes de consolidar programas repetidos dentro del mismo periodo.');
        }
        $this->comment('El reporte no imprime datos personales y no conecta ni escribe en ninguna base de datos.');

        return self::SUCCESS;
    }

    /** @return array<string, list<array<string, string|null>>> */
    private function parse(string $sql): array
    {
        $schemas = [];
        if (preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/is', $sql, $schemaMatches, PREG_SET_ORDER)) {
            foreach ($schemaMatches as $match) {
                preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $columnMatches);
                $schemas[strtolower($match[1])] = array_map('strtolower', $columnMatches[1]);
            }
        }

        $tables = [];
        if (! preg_match_all('/INSERT\s+INTO\s+`([^`]+)`/i', $sql, $insertMatches, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('El respaldo no contiene INSERT reconocibles.');
        }

        foreach ($insertMatches[0] as $index => [$token, $offset]) {
            $table = strtolower($insertMatches[1][$index][0]);
            if (! in_array($table, self::TABLAS, true)) {
                continue;
            }

            $after = $offset + strlen($token);
            $valuesOffset = stripos($sql, 'VALUES', $after);
            if ($valuesOffset === false) {
                continue;
            }
            $header = substr($sql, $after, $valuesOffset - $after);
            $columns = $this->insertColumns($header) ?: ($schemas[$table] ?? []);
            if ($columns === []) {
                continue;
            }

            $statement = $this->readTuples($sql, $valuesOffset + 6);
            foreach ($statement as $tuple) {
                $values = array_map($this->decode(...), $this->splitFields($tuple));
                if (count($values) === count($columns)) {
                    $tables[$table][] = array_combine($columns, $values);
                }
            }
        }

        return $tables;
    }

    /** @param array<string, list<array<string, string|null>>> $data
     * @return list<int>
     */
    private function auditReferences(array $data): array
    {
        $idSets = [];
        foreach (['departamentos', 'provincias', 'distritos', 'grados', 'facultads', 'concepto_pagos', 'programas', 'postulantes', 'pre_inscripcions', 'inscripcions', 'vouchers', 'tabla_voucher'] as $table) {
            $idSets[$table] = array_fill_keys(array_map('strval', array_column($data[$table] ?? [], 'id')), true);
        }

        $programOrphans = $this->countMissing($data['programas'] ?? [], 'grado_id', $idSets['grados'])
            + $this->countMissing($data['programas'] ?? [], 'facultad_id', $idSets['facultads']);
        $preOrphans = $this->countMissing($data['pre_inscripcions'] ?? [], 'programa_id', $idSets['programas'])
            + $this->countMissing($data['pre_inscripcions'] ?? [], 'distrito_id', $idSets['distritos']);

        $registrationOrphans = $this->countMissing($data['inscripcions'] ?? [], 'postulante_id', $idSets['postulantes'])
            + $this->countMissing($data['inscripcions'] ?? [], 'programa_id', $idSets['programas']);
        if (isset($data['inscripcions'][0]['voucher_id'])) {
            $registrationOrphans += $this->countMissing($data['inscripcions'] ?? [], 'voucher_id', $idSets['vouchers']);
        }

        $legacyVoucherOrphans = 0;
        if (isset($data['inscripcions'][0]['cod_voucher'])) {
            $voucherNumbers = array_fill_keys(array_map(
                static fn ($row) => trim((string) ($row['nro_voucher'] ?? '')),
                $data['tabla_voucher'] ?? []
            ), true);
            foreach ($data['inscripcions'] ?? [] as $row) {
                $number = trim((string) ($row['cod_voucher'] ?? ''));
                if ($number !== '' && ! isset($voucherNumbers[$number])) {
                    $legacyVoucherOrphans++;
                }
            }
        }

        $documentOrphans = $this->countMissing($data['documentos'] ?? [], 'postulante_id', $idSets['postulantes']);
        $noteOrphans = $this->countMissing($data['notas'] ?? [], 'inscripcion_id', $idSets['inscripcions']);

        return [$programOrphans, $preOrphans, $registrationOrphans, $legacyVoucherOrphans, $documentOrphans, $noteOrphans];
    }

    /** @param list<array<string, string|null>> $rows @param array<string, true> $ids */
    private function countMissing(array $rows, string $column, array $ids): int
    {
        $missing = 0;
        foreach ($rows as $row) {
            $value = $row[$column] ?? null;
            if ($value !== null && $value !== '' && ! isset($ids[(string) $value])) {
                $missing++;
            }
        }

        return $missing;
    }

    /** @param list<array<string, string|null>> $rows @return array<string, array<string, string|null>> */
    private function lookupById(array $rows): array
    {
        $lookup = [];
        foreach ($rows as $row) {
            $lookup[(string) ($row['id'] ?? '')] = $row;
        }

        return $lookup;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)), 'UTF-8');
    }

    /** @param list<array<string, string|null>> $rows @return array<string, int> */
    private function distribution(array $rows, string $column): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (! array_key_exists($column, $row)) {
                continue;
            }
            $value = $row[$column] === null ? 'NULL' : trim((string) $row[$column]);
            $values[$value] = ($values[$value] ?? 0) + 1;
        }
        ksort($values);
        return $values;
    }

    /** @param array<string, int> $distribution */
    private function formatDistribution(array $distribution): string
    {
        return implode(', ', array_map(
            static fn ($value, $count) => ($value === '' ? '(vacío)' : $value) . ': ' . $count,
            array_keys($distribution),
            array_values($distribution)
        ));
    }

    /** @return list<string> */
    private function insertColumns(string $header): array
    {
        if (! preg_match('/\((.*?)\)\s*$/s', trim($header), $match)) {
            return [];
        }
        preg_match_all('/`([^`]+)`/', $match[1], $columns);
        return array_map('strtolower', $columns[1]);
    }

    /** @return list<string> */
    private function readTuples(string $sql, int $offset): array
    {
        $tuples = [];
        $depth = 0;
        $quote = null;
        $escaped = false;
        $start = null;
        for ($i = $offset, $length = strlen($sql); $i < $length; $i++) {
            $char = $sql[$i];
            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    if (($sql[$i + 1] ?? null) === $quote) {
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
                    $start = $i + 1;
                }
                $depth++;
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0 && $start !== null) {
                    $tuples[] = substr($sql, $start, $i - $start);
                    $start = null;
                }
            } elseif ($char === ';' && $depth === 0) {
                break;
            }
        }
        return $tuples;
    }

    /** @return list<string> */
    private function splitFields(string $tuple): array
    {
        $fields = [];
        $depth = 0;
        $quote = null;
        $escaped = false;
        $start = 0;
        for ($i = 0, $length = strlen($tuple); $i < $length; $i++) {
            $char = $tuple[$i];
            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    if (($tuple[$i + 1] ?? null) === $quote) {
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

    private function decode(string $value): ?string
    {
        $value = trim($value);
        if (strtoupper($value) === 'NULL') {
            return null;
        }
        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            return str_replace(
                ["\\0", "\\b", "\\n", "\\r", "\\t", "\\Z", "\\'", '\\"', "''", '\\\\'],
                ["\0", "\b", "\n", "\r", "\t", "\x1a", "'", '"', "'", '\\'],
                substr($value, 1, -1)
            );
        }
        return $value;
    }
}
