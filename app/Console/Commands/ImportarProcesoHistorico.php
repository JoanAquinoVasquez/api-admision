<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Importa un proceso de admisión histórico a db_sistema_admision.
 *
 * Modos de fuente:
 *   --fuente=mysql   Lee desde una conexión MySQL viva (útil para 2026-I).
 *   --fuente=sql     Lee desde un backup SQL en bds/ (útil para 2024-II y 2025-I).
 *
 * Por defecto opera en modo dry-run (solo reporta, no escribe).
 * Pasar --aplicar para ejecutar la importación real.
 *
 * Uso:
 *   php artisan admision:importar-proceso 2026-I --fuente=mysql --conexion=mysql
 *   php artisan admision:importar-proceso 2025-I  --fuente=sql
 *   php artisan admision:importar-proceso 2024-II --fuente=sql
 */
class ImportarProcesoHistorico extends Command
{
    protected $signature = 'admision:importar-proceso
                            {periodo            : Código del proceso, ej. 2026-I, 2025-I, 2024-II}
                            {--fuente=sql       : Origen de los datos: "mysql" o "sql"}
                            {--conexion=mysql   : Nombre de la conexión Laravel cuando --fuente=mysql}
                            {--aplicar          : Confirma la escritura en db_sistema_admision}';

    protected $description = 'Importa un proceso de admisión histórico a db_sistema_admision desde una BD viva o un backup SQL.';

    /** Conexión destino — siempre db_sistema_admision */
    private const DESTINO = 'mysql_unificada';

    /** Mapeo periodo → nombre del archivo SQL en bds/ */
    private const ARCHIVOS_SQL = [
        '2024-II' => 'epgunpr689_inscripcion_epg.sql',
        '2025-I'  => 'epgunpr689_db_admision2025I.sql',
        '2026-I'  => 'epgunpr689_db_admision2026I.sql',
    ];

    /** Tablas del catálogo compartido (no son por-proceso) */
    private const TABLAS_CATALOGO = [
        'departamentos',
        'provincias',
        'distritos',
        'grados',
        'facultads',
        'concepto_pagos',
        'docentes',
    ];

    /** Tablas operativas con proceso_admision_id */
    private const TABLAS_OPERATIVAS = [
        'programas',
        'postulantes',
        'pre_inscripcions',
        'vouchers',
        'inscripcions',
        'documentos',
        'notas',
        'comision_admisions',
        'correos',
    ];

    /**
     * Tablas de catálogo que NO tienen columnas created_at / updated_at.
     * (Su migración tiene timestamps() comentado.)
     */
    private const TABLAS_SIN_TIMESTAMPS = [
        'departamentos',
        'provincias',
        'distritos',
        'grados',
        'facultads',
    ];

    // ------------------------------------------------------------------ //
    //  Mapa de IDs: id_origen → id_destino, por tabla                     //
    // ------------------------------------------------------------------ //
    private array $idMap = [];
    private string $periodoActual = '';

    // ------------------------------------------------------------------ //
    //  handle()                                                            //
    // ------------------------------------------------------------------ //

    public function handle(): int
    {
        $periodo   = $this->argument('periodo');
        $this->periodoActual = $periodo;
        $fuente    = $this->option('fuente');
        $conexion  = $this->option('conexion');
        $aplicar   = (bool) $this->option('aplicar');

        if (! in_array($fuente, ['mysql', 'sql'], true)) {
            $this->error("--fuente debe ser 'mysql' o 'sql'.");
            return self::FAILURE;
        }

        $this->info("=== Importación de proceso {$periodo} ===");
        $this->line("Fuente   : {$fuente}" . ($fuente === 'mysql' ? " (conexión: {$conexion})" : ''));
        $this->line('Destino  : ' . self::DESTINO . ' (db_sistema_admision)');
        $this->line('Modo     : ' . ($aplicar ? '<fg=red>APLICAR — escribirá en la BD</>' : '<fg=yellow>DRY-RUN — solo lectura</>'));
        $this->newLine();

        try {
            $data = $fuente === 'mysql'
                ? $this->leerDesdeMySQL($conexion)
                : $this->leerDesdeSQL($periodo);
        } catch (Throwable $e) {
            $this->error('Error al leer los datos: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->reportarConteos($data, $periodo);
        $this->reportarTransformaciones($data, $periodo);
        $destinoListo = $this->reportarEstadoDestino($periodo, $data);

        if (! $aplicar) {
            $this->newLine();
            $this->comment('Dry-run completado. No se escribió nada. Pasa --aplicar para ejecutar.');
            return self::SUCCESS;
        }

        if (! $destinoListo) {
            $this->error('No se puede aplicar: hay referencias de origen que no encuentran correspondencia segura en el destino.');
            return self::FAILURE;
        }

        if (! $this->confirm("¿Confirmas importar el proceso {$periodo} en db_sistema_admision?")) {
            $this->comment('Importación cancelada.');
            return self::SUCCESS;
        }

        return $this->ejecutarImportacion($periodo, $data);
    }

    // ------------------------------------------------------------------ //
    //  Lectura de datos                                                    //
    // ------------------------------------------------------------------ //

    /**
     * Lee todas las tablas relevantes desde una conexión MySQL viva.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function leerDesdeMySQL(string $conexion): array
    {
        $db   = DB::connection($conexion);
        $data = [];

        $tablas = array_unique(array_merge(self::TABLAS_CATALOGO, self::TABLAS_OPERATIVAS));

        foreach ($tablas as $tabla) {
            try {
                $data[$tabla] = $db->table($tabla)->get()->map(fn ($r) => (array) $r)->all();
                $this->line("  Leída: <fg=green>{$tabla}</> — " . count($data[$tabla]) . ' filas');
            } catch (Throwable $e) {
                $this->warn("  Tabla '{$tabla}' no encontrada o vacía en origen: " . $e->getMessage());
                $data[$tabla] = [];
            }
        }

        return $data;
    }

    /**
     * Lee todas las tablas relevantes parseando un backup SQL.
     * Reutiliza el parser de AuditarRelacionesRespaldos.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function leerDesdeSQL(string $periodo): array
    {
        $fileName = self::ARCHIVOS_SQL[$periodo] ?? null;
        if ($fileName === null) {
            throw new \RuntimeException("No hay archivo SQL configurado para el periodo '{$periodo}'.");
        }

        $path = base_path('../bds/' . $fileName);
        $sql  = is_file($path) ? file_get_contents($path) : false;

        if ($sql === false) {
            throw new \RuntimeException("No se pudo leer el archivo: {$path}");
        }

        $this->line("Leyendo backup SQL: {$fileName} (" . round(strlen($sql) / 1024 / 1024, 1) . ' MB)');

        $tablas = array_merge(
            self::TABLAS_CATALOGO,
            self::TABLAS_OPERATIVAS,
            ['tabla_voucher']
        );

        return $this->normalizarFuenteSql($periodo, $this->parsearSQL($sql, $tablas));
    }

    /** @param array<string, list<array<string, mixed>>> $data
     *  @return array<string, list<array<string, mixed>>>
     */
    private function normalizarFuenteSql(string $periodo, array $data): array
    {
        if ($periodo !== '2024-II') {
            return $data;
        }

        foreach ($data['provincias'] ?? [] as &$row) {
            $row['departamento_id'] ??= $row['id_departamento'] ?? null;
        }
        unset($row);
        foreach ($data['distritos'] ?? [] as &$row) {
            $row['provincia_id'] ??= $row['id_provincia'] ?? null;
        }
        unset($row);

        $documents = [];
        foreach ($data['postulantes'] ?? [] as $person) {
            foreach (['rutadni' => 'identidad', 'foto' => 'foto', 'cv' => 'cv'] as $column => $type) {
                $url = trim((string) ($person[$column] ?? ''));
                if ($url !== '') {
                    $documents[] = [
                        'id' => 'p:' . ($person['id'] ?? '0') . ':' . $column,
                        'postulante_id' => $person['id'] ?? null,
                        'tipo' => $type,
                        'nombre_archivo' => basename(str_replace('\\', '/', $url)),
                        'url' => $url,
                        'estado' => 1,
                        'created_at' => $person['created_at'] ?? null,
                        'updated_at' => $person['updated_at'] ?? null,
                    ];
                }
            }
        }
        foreach ($data['inscripcions'] ?? [] as $registration) {
            $url = trim((string) ($registration['voucher'] ?? ''));
            if ($url !== '') {
                $documents[] = [
                    'id' => 'i:' . ($registration['id'] ?? '0') . ':voucher',
                    'postulante_id' => $registration['postulante_id'] ?? null,
                    'tipo' => 'voucher',
                    'nombre_archivo' => basename(str_replace('\\', '/', $url)),
                    'url' => $url,
                    'estado' => 1,
                    'created_at' => $registration['created_at'] ?? null,
                    'updated_at' => $registration['updated_at'] ?? null,
                ];
            }
        }
        if ($documents !== []) {
            $data['documentos'] = $documents;
        }

        return $data;
    }

    // ------------------------------------------------------------------ //
    //  Importación                                                         //
    // ------------------------------------------------------------------ //

    private function ejecutarImportacion(string $periodo, array $data): int
    {
        $destino = DB::connection(self::DESTINO);
        $esLegado = isset($data['tabla_voucher']) && count($data['tabla_voucher']) > 0;

        try {
            $destino->transaction(function () use ($destino, $periodo, $data, $esLegado): void {

                // 1. Crear o recuperar el ProcesoAdmision en destino
                $procesoId = $this->upsertProceso($destino, $periodo);
                $this->line("<fg=cyan>Proceso '{$periodo}' → ID {$procesoId} en destino</>");

                // 2. Catálogo compartido (departamentos, provincias, distritos, grados, facultads, concepto_pagos)
                foreach (self::TABLAS_CATALOGO as $tabla) {
                    $this->importarCatalogo($destino, $tabla, $data[$tabla] ?? []);
                }

                // 3. Programas (catálogo de programas, deduplicado por nombre+grado+facultad)
                $this->importarProgramas($destino, $procesoId, $data['programas'] ?? [], $data);

                // Comisión y docentes se conservan como entidades de acceso/operación del periodo.
                $this->importarComision($destino, $procesoId, $data['comision_admisions'] ?? []);

                // 4. Postulantes (deduplicados por num_iden/DNI)
                $this->importarPostulantes($destino, $data['postulantes'] ?? []);

                // 5. Pre-inscripciones
                $this->importarPreInscripciones($destino, $procesoId, $data['pre_inscripcions'] ?? []);

                // 6. Vouchers
                if ($esLegado) {
                    $this->importarVouchersLegado($destino, $procesoId, $data['tabla_voucher'] ?? [], $data['concepto_pagos'] ?? []);
                } else {
                    $this->importarVouchers($destino, $procesoId, $data['vouchers'] ?? []);
                }

                // 7. Inscripciones
                $this->importarInscripciones($destino, $procesoId, $data['inscripcions'] ?? [], $esLegado);

                // 7b. El respaldo 2024-II conserva un log antiguo de correo por postulante.
                if ($esLegado) {
                    $this->importarCorreosLegado($destino, $data['correos'] ?? [], $data['inscripcions'] ?? []);
                } else {
                    $this->importarCorreos($destino, $data['correos'] ?? []);
                }

                // 8. Documentos
                $this->importarDocumentos($destino, $procesoId, $data['documentos'] ?? []);

                // 9. Notas
                $this->importarNotas($destino, $data['notas'] ?? []);

            });
        } catch (Throwable $e) {
            $this->error('La importación se revirtió por un error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Proceso {$periodo} importado correctamente en db_sistema_admision.");
        return self::SUCCESS;
    }

    // ------------------------------------------------------------------ //
    //  Helpers de importación por tabla                                   //
    // ------------------------------------------------------------------ //

    private function upsertProceso(\Illuminate\Database\Connection $db, string $periodo): int
    {
        $nombres = [
            '2024-II' => 'Admisión EPG 2024-II',
            '2025-I'  => 'Admisión EPG 2025-I',
            '2026-I'  => 'Admisión EPG 2026-I',
            '2027-I'  => 'Admisión EPG 2027-I',
        ];

        $existing = $db->table('procesos_admision')->where('codigo', $periodo)->first(['id']);
        if ($existing) {
            return $existing->id;
        }

        return $db->table('procesos_admision')->insertGetId([
            'codigo'      => $periodo,
            'nombre'      => $nombres[$periodo] ?? "Admisión EPG {$periodo}",
            'estado'      => 'cerrado',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Catálogo compartido: inserta si no existe ya (por clave de negocio).
     * Actualiza el idMap[tabla][id_origen] = id_destino.
     */
    private function importarCatalogo(\Illuminate\Database\Connection $db, string $tabla, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $tieneTimestamps = ! in_array($tabla, self::TABLAS_SIN_TIMESTAMPS, true);

        // Claves de negocio por tabla para evitar duplicados
        $claveNegocio = match ($tabla) {
            'departamentos'  => ['nombre'],
            'provincias'     => ['nombre', 'departamento_id'],
            'distritos'      => ['nombre', 'provincia_id'],
            'grados'         => ['nombre'],
            'facultads'      => ['nombre'],
            'concepto_pagos' => ['cod_concepto', 'nombre', 'monto'],
            'docentes'       => ['dni'],
            default          => ['nombre'],
        };

        // Columnas permitidas por tabla (solo las que existen en el esquema destino)
        $columnasFiltradas = match ($tabla) {
            'departamentos'  => ['nombre'],
            'provincias'     => ['departamento_id', 'nombre'],
            'distritos'      => ['provincia_id', 'nombre'],
            'grados'         => ['nombre'],
            'facultads'      => ['nombre', 'siglas', 'estado'],
            'concepto_pagos' => ['cod_concepto', 'nombre', 'monto', 'estado'],
            'docentes'       => ['nombres', 'ap_paterno', 'ap_materno', 'dni', 'email', 'tipo', 'password', 'estado'],
            default          => null, // null = usar todas
        };

        $importados = 0;
        foreach ($filas as $fila) {
            $idOrigen = (int) ($fila['id'] ?? 0);
            $payload  = $this->remapFksCatalogo($db, $tabla, $fila);

            // Filtrar solo las columnas que existen en el destino
            if ($columnasFiltradas !== null) {
                $payload = array_intersect_key($payload, array_flip($columnasFiltradas));
            }

            if ($tabla === 'facultads') {
                $payload['siglas'] ??= mb_strtoupper(mb_substr((string) ($payload['nombre'] ?? 'FACULTAD'), 0, 10));
                $payload['estado'] ??= true;
            }
            if ($tabla === 'docentes') {
                $payload['tipo'] ??= 'cv';
                $payload['estado'] ??= true;
            }

            // Buscar si ya existe por clave de negocio
            $query = $db->table($tabla);
            foreach ($claveNegocio as $col) {
                if (isset($payload[$col])) {
                    $query->where($col, $payload[$col]);
                }
            }
            $existente = $query->first(['id']);

            if ($existente) {
                $this->idMap[$tabla][$idOrigen] = $existente->id;
                $this->registrarMapeo($db, $tabla, (string) $idOrigen, (int) $existente->id);
            } else {
                unset($payload['id']);

                if ($tieneTimestamps) {
                    $payload['created_at'] ??= now();
                    $payload['updated_at'] ??= now();
                }

                $idDestino = $db->table($tabla)->insertGetId($payload);
                $this->idMap[$tabla][$idOrigen] = $idDestino;
                $this->registrarMapeo($db, $tabla, (string) $idOrigen, $idDestino);
                $importados++;
            }
        }

        $this->line("  Catálogo <fg=green>{$tabla}</>: {$importados} nuevos, " . (count($filas) - $importados) . ' existentes.');
    }

    private function importarProgramas(\Illuminate\Database\Connection $db, int $procesoId, array $filas, array $allData): void
    {
        if (empty($filas)) {
            return;
        }

        $importados = 0;
        $reutilizados = 0;

        foreach ($filas as $fila) {
            $idOrigen  = (int) ($fila['id'] ?? 0);
            $nombre    = trim((string) ($fila['nombre'] ?? ''));
            $gradoId   = $this->idMap['grados'][(int)($fila['grado_id'] ?? 0)] ?? null;
            $facultadId = $this->idMap['facultads'][(int)($fila['facultad_id'] ?? 0)] ?? null;

            if (! $gradoId || ! $facultadId || $nombre === '') {
                $this->warn("  Programa sin grado/facultad/nombre, omitido (origen id={$idOrigen}).");
                continue;
            }

            // Buscar por identidad de catálogo
            $existente = $db->table('programas')
                ->where('nombre', $nombre)
                ->where('grado_id', $gradoId)
                ->where('facultad_id', $facultadId)
                ->first(['id']);

            if ($existente) {
                $this->idMap['programas'][$idOrigen] = $existente->id;
                $this->registrarMapeo($db, 'programas', (string) $idOrigen, (int) $existente->id);
                $reutilizados++;
            } else {
                $idDestino = $db->table('programas')->insertGetId([
                    'nombre'      => $nombre,
                    'grado_id'    => $gradoId,
                    'facultad_id' => $facultadId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $this->idMap['programas'][$idOrigen] = $idDestino;
                $this->registrarMapeo($db, 'programas', (string) $idOrigen, $idDestino);
                $importados++;
            }

            // Crear oferta del programa para este proceso (programas_procesos)
            $programaDestinoId = $this->idMap['programas'][$idOrigen];
            $yaEnProceso = $db->table('programas_procesos')
                ->where('proceso_admision_id', $procesoId)
                ->where('programa_id', $programaDestinoId)
                ->exists();

            if (! $yaEnProceso) {
                $conceptoPagoId = isset($fila['concepto_pago_id'])
                    ? ($this->idMap['concepto_pagos'][(int)$fila['concepto_pago_id']] ?? null)
                    : null;

                $db->table('programas_procesos')->insert([
                    'proceso_admision_id' => $procesoId,
                    'programa_id'         => $programaDestinoId,
                    'estado'              => (bool) ($fila['estado'] ?? true) ? 'habilitado' : 'inhabilitado',
                    'vacantes'            => $fila['vacantes'] ?? null,
                    'concepto_pago_id'    => $conceptoPagoId,
                    'duracion_meses'      => $fila['duracion_meses'] ?? null,
                    'modalidad'           => $fila['modalidad'] ?? null,
                    'plan_estudio'        => $fila['plan_estudio'] ?? null,
                    'brochure'            => $fila['brochure'] ?? null,
                    'docente_id'          => $this->idMap['docentes'][(int) ($fila['docente_id'] ?? 0)] ?? null,
                    'docente_entrevista_id' => $this->idMap['docentes'][(int) ($fila['docente_entrevista_id'] ?? 0)] ?? null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }

        $this->line("  Programas: {$importados} nuevos en catálogo, {$reutilizados} reutilizados, ofertas en proceso creadas.");
    }

    private function importarPostulantes(\Illuminate\Database\Connection $db, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $importados   = 0;
        $reutilizados = 0;
        $omitidos     = 0;

        foreach ($filas as $fila) {
            $idOrigen = (int) ($fila['id'] ?? 0);
            $numIden  = mb_strtoupper(trim((string) ($fila['num_iden'] ?? $fila['dni'] ?? '')));

            if ($numIden === '') {
                $omitidos++;
                continue;
            }

            // Deduplicar por DNI — postulantes son personas físicas compartidas entre procesos
            $existente = $db->table('postulantes')->where('num_iden', $numIden)->first(['id']);

            if ($existente) {
                $this->idMap['postulantes'][$idOrigen] = $existente->id;
                $this->registrarMapeo($db, 'postulantes', (string) $idOrigen, (int) $existente->id);
                $reutilizados++;
                continue;
            }

            $distritoId = $this->idMap['distritos'][(int)($fila['distrito_id'] ?? 0)]
                ?? $this->resolverDistritoLegado($db, $fila);
            if ($distritoId === null) {
                $omitidos++;
                continue;
            }

            $idDestino = $db->table('postulantes')->insertGetId([
                'distrito_id'      => $distritoId,
                'nombres'          => mb_strtoupper(trim((string) ($fila['nombres'] ?? 'SIN NOMBRE'))),
                'ap_paterno'       => mb_strtoupper(trim((string) ($fila['ap_paterno'] ?? $fila['ap'] ?? 'S/P'))),
                'ap_materno'       => mb_strtoupper(trim((string) ($fila['ap_materno'] ?? $fila['am'] ?? 'S/M'))),
                'email'            => strtolower(trim((string) ($fila['email'] ?? $fila['correo'] ?? ''))),
                'tipo_doc'         => $fila['tipo_doc'] ?? 'DNI',
                'num_iden'         => $numIden,
                'fecha_nacimiento' => $fila['fecha_nacimiento'] ?? '1900-01-01',
                'sexo'             => $this->normalizarSexo($fila['sexo'] ?? null),
                'celular'          => $fila['celular'] ?? '',
                'direccion'        => $fila['direccion'] ?? '',
                'estado'           => (bool) ($fila['estado'] ?? true),
                'created_at'       => $fila['created_at'] ?? now(),
                'updated_at'       => $fila['updated_at'] ?? now(),
            ]);

            $this->idMap['postulantes'][$idOrigen] = $idDestino;
            $this->registrarMapeo($db, 'postulantes', (string) $idOrigen, $idDestino);
            $importados++;
        }

        $this->line("  Postulantes: {$importados} nuevos, {$reutilizados} reutilizados por DNI, {$omitidos} omitidos sin DNI.");
    }

    private function importarPreInscripciones(\Illuminate\Database\Connection $db, int $procesoId, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $importados = 0;
        $omitidos   = 0;

        foreach ($filas as $fila) {
            $idOrigen   = (int) ($fila['id'] ?? 0);
            $numIden    = mb_strtoupper(trim((string) ($fila['num_iden'] ?? '')));
            $programaId = $this->idMap['programas'][(int)($fila['programa_id'] ?? 0)] ?? null;

            if ($numIden === '' || $programaId === null) {
                $omitidos++;
                continue;
            }

            // Evitar duplicado (proceso + DNI)
            $existe = $db->table('pre_inscripcions')
                ->where('proceso_admision_id', $procesoId)
                ->where('num_iden', $numIden)
                ->exists();

            if ($existe) {
                $existingId = $db->table('pre_inscripcions')
                    ->where('proceso_admision_id', $procesoId)
                    ->where('num_iden', $numIden)
                    ->value('id');
                if ($existingId !== null) {
                    $this->registrarMapeo($db, 'pre_inscripcions', (string) $idOrigen, (int) $existingId);
                }
                $omitidos++;
                continue;
            }

            $distritoId = $this->idMap['distritos'][(int)($fila['distrito_id'] ?? 0)]
                ?? $db->table('distritos')->value('id');
            if ($distritoId === null) {
                $omitidos++;
                continue;
            }

            $postulanteId = $this->idMap['postulantes'][(int) ($fila['postulante_id'] ?? 0)] ?? null;
            if ($postulanteId === null) {
                $postulanteId = $db->table('postulantes')->where('num_iden', $numIden)->value('id');
            }

            $db->table('pre_inscripcions')->insert([
                'proceso_admision_id' => $procesoId,
                'postulante_id'       => $postulanteId,
                'programa_id'         => $programaId,
                'distrito_id'         => $distritoId,
                'nombres'             => mb_strtoupper(trim((string) ($fila['nombres'] ?? 'SIN NOMBRE'))),
                'ap_paterno'          => mb_strtoupper(trim((string) ($fila['ap_paterno'] ?? 'S/P'))),
                'ap_materno'          => mb_strtoupper(trim((string) ($fila['ap_materno'] ?? 'S/M'))),
                'num_iden'            => $numIden,
                'tipo_doc'            => $fila['tipo_doc'] ?? 'DNI',
                'email'               => strtolower(trim((string) ($fila['email'] ?? ''))),
                'uni_procedencia'     => $fila['uni_procedencia'] ?? null,
                'centro_trabajo'      => $fila['centro_trabajo'] ?? null,
                'cargo'               => $fila['cargo'] ?? null,
                'celular'             => $fila['celular'] ?? '',
                'fecha_nacimiento'    => $fila['fecha_nacimiento'] ?? '1900-01-01',
                'sexo'                => $this->normalizarSexo($fila['sexo'] ?? null),
                'estado'              => (bool) ($fila['estado'] ?? true),
                'created_at'          => $fila['created_at'] ?? now(),
                'updated_at'          => $fila['updated_at'] ?? now(),
            ]);

            $importados++;
        }

        $this->line("  Pre-inscripciones: {$importados} importadas, {$omitidos} omitidas (duplicadas o incompletas).");
    }

    private function importarVouchers(\Illuminate\Database\Connection $db, int $procesoId, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $importados = 0;

        foreach ($filas as $fila) {
            $idOrigen       = (int) ($fila['id'] ?? 0);
            $mappedId = $this->buscarMapeo($db, 'vouchers', (string) $idOrigen);
            if ($mappedId !== null) {
                $this->idMap['vouchers'][$idOrigen] = $mappedId;
                continue;
            }
            $conceptoPagoId = $this->idMap['concepto_pagos'][(int)($fila['concepto_pago_id'] ?? 0)] ?? null;

            if ($conceptoPagoId === null) {
                continue;
            }

            $idDestino = $db->table('vouchers')->insertGetId([
                'proceso_admision_id' => $procesoId,
                'concepto_pago_id'    => $conceptoPagoId,
                'numero'              => substr((string) ($fila['numero'] ?? ''), 0, 7),
                'num_iden'            => mb_strtoupper(trim((string) ($fila['num_iden'] ?? ''))),
                'nombre_completo'     => substr((string) ($fila['nombre_completo'] ?? ''), 0, 150),
                'monto'               => (float) ($fila['monto'] ?? 0),
                'fecha_pago'          => $fila['fecha_pago'] ?? now()->toDateString(),
                'hora_pago'           => $fila['hora_pago'] ?? '00:00:00',
                'cajero'              => substr((string) ($fila['cajero'] ?? ''), 0, 4),
                'agencia'             => substr((string) ($fila['agencia'] ?? ''), 0, 4),
                'estado'              => (bool) ($fila['estado'] ?? true),
                'created_at'          => $fila['created_at'] ?? now(),
                'updated_at'          => $fila['updated_at'] ?? now(),
            ]);

            $this->idMap['vouchers'][$idOrigen] = $idDestino;
            $this->registrarMapeo($db, 'vouchers', (string) $idOrigen, $idDestino);
            $importados++;
        }

        $this->line("  Vouchers: {$importados} importados.");
    }

    /**
     * Convierte el esquema legado de 2024-II (tabla_voucher) al formato moderno (vouchers).
     * En 2024-II no hay concepto_pago_id en tabla_voucher; se usa el primer concepto disponible
     * o se crea uno genérico para el periodo.
     */
    private function importarVouchersLegado(
        \Illuminate\Database\Connection $db,
        int $procesoId,
        array $vouchersLegado,
        array $conceptosPago
    ): void {
        if (empty($vouchersLegado)) {
            return;
        }

        $importados = 0;
        foreach ($vouchersLegado as $voucher) {
            $idOrigen  = (int) ($voucher['id'] ?? 0);
            $mappedId = $this->buscarMapeo($db, 'vouchers', (string) $idOrigen);
            if ($mappedId !== null) {
                $this->idMap['vouchers'][$idOrigen] = $mappedId;
                $this->idMap['vouchers_por_numero'][trim((string) ($voucher['nro_voucher'] ?? ''))] = $mappedId;
                continue;
            }
            $nroVoucher = substr(trim((string) ($voucher['nro_voucher'] ?? '')), 0, 7);
            $codPago = trim((string) ($voucher['cod_pago'] ?? 'ADM-2024'));
            $monto = min(abs((float) ($voucher['monto'] ?? 0)), 9999.99);
            $conceptoPagoDestinoId = $db->table('concepto_pagos')
                ->where('cod_concepto', $codPago)
                ->where('monto', $monto)
                ->value('id');
            if ($conceptoPagoDestinoId === null) {
                $conceptoPagoDestinoId = $db->table('concepto_pagos')->insertGetId([
                    'cod_concepto' => $codPago,
                    'nombre'       => "Pago de admisión 2024-II ({$codPago})",
                    'monto'        => $monto,
                    'estado'       => 1,
                    'created_at'   => $voucher['created_at'] ?? now(),
                    'updated_at'   => $voucher['updated_at'] ?? now(),
                ]);
            }

            $idDestino = $db->table('vouchers')->insertGetId([
                'proceso_admision_id' => $procesoId,
                'concepto_pago_id'    => $conceptoPagoDestinoId,
                'numero'              => $nroVoucher,
                'num_iden'            => mb_strtoupper(substr(trim((string) ($voucher['dni'] ?? '')), 0, 20)),
                'nombre_completo'     => substr(trim((string) ($voucher['nombre_completo'] ?? '')), 0, 150),
                'monto'               => $monto,
                'fecha_pago'          => $voucher['fecha_pago'] ?? $voucher['fecha'] ?? now()->toDateString(),
                'hora_pago'           => $voucher['hora_pago'] ?? $voucher['hora'] ?? '00:00:00',
                'cajero'              => substr(trim((string) ($voucher['cajero'] ?? '')), 0, 4),
                'agencia'             => substr(trim((string) ($voucher['agencia'] ?? '')), 0, 4),
                'estado'              => (bool) ($voucher['estado'] ?? true),
                'created_at'          => $voucher['created_at'] ?? now(),
                'updated_at'          => $voucher['updated_at'] ?? now(),
            ]);

            $this->idMap['vouchers_por_numero'][$nroVoucher] = $idDestino;
            $this->idMap['vouchers'][$idOrigen]              = $idDestino;
            $this->registrarMapeo($db, 'vouchers', (string) $idOrigen, $idDestino);
            $importados++;
        }

        $this->line("  Vouchers legado (tabla_voucher → vouchers): {$importados} importados.");
    }

    private function importarInscripciones(
        \Illuminate\Database\Connection $db,
        int $procesoId,
        array $filas,
        bool $esLegado
    ): void {
        if (empty($filas)) {
            return;
        }

        $importados = 0;
        $omitidos   = 0;

        foreach ($filas as $fila) {
            $idOrigen    = (int) ($fila['id'] ?? 0);
            $mappedId = $this->buscarMapeo($db, 'inscripcions', (string) $idOrigen);
            if ($mappedId !== null) {
                $this->idMap['inscripcions'][$idOrigen] = $mappedId;
                continue;
            }
            $postulanteId = $this->idMap['postulantes'][(int)($fila['postulante_id'] ?? 0)] ?? null;
            $programaId   = $this->idMap['programas'][(int)($fila['programa_id'] ?? 0)] ?? null;

            if ($postulanteId === null || $programaId === null) {
                $omitidos++;
                continue;
            }

            // Resolver voucher_id
            if ($esLegado) {
                // 2024-II: enlaza por cod_voucher (número de voucher)
                $codVoucher = trim((string) ($fila['cod_voucher'] ?? ''));
                $voucherId  = $this->idMap['vouchers_por_numero'][$codVoucher] ?? null;
            } else {
                $voucherId = $this->idMap['vouchers'][(int)($fila['voucher_id'] ?? 0)] ?? null;
            }

            if ($voucherId === null) {
                $omitidos++;
                continue;
            }

            $idDestino = $db->table('inscripcions')->insertGetId([
                'proceso_admision_id' => $procesoId,
                'postulante_id'       => $postulanteId,
                'programa_id'         => $programaId,
                'voucher_id'          => $voucherId,
                'codigo'              => substr((string) ($fila['codigo'] ?? $fila['cod_voucher'] ?? ''), 0, 7),
                'val_digital'         => (int) ($fila['val_digital'] ?? $fila['validado'] ?? 0),
                'val_fisico'          => (bool) ($fila['val_fisico'] ?? false),
                'observacion'         => $this->observacionImportada($fila),
                'estado'              => $esLegado ? 1 : ($fila['estado'] ?? 1),
                'created_at'          => $fila['created_at'] ?? now(),
                'updated_at'          => $fila['updated_at'] ?? now(),
            ]);

            $this->idMap['inscripcions'][$idOrigen] = $idDestino;
            $this->registrarMapeo($db, 'inscripcions', (string) $idOrigen, $idDestino);
            $importados++;
        }

        $this->line("  Inscripciones: {$importados} importadas, {$omitidos} omitidas (sin postulante/programa/voucher).");
    }

    private function importarDocumentos(\Illuminate\Database\Connection $db, int $procesoId, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $importados = 0;
        $omitidos   = 0;

        foreach ($filas as $fila) {
            $sourceId = (string) ($fila['id'] ?? '');
            if ($sourceId !== '' && $this->buscarMapeo($db, 'documentos', $sourceId) !== null) {
                continue;
            }

            $postulanteId = $this->idMap['postulantes'][(int)($fila['postulante_id'] ?? 0)] ?? null;

            if ($postulanteId === null) {
                $omitidos++;
                continue;
            }

            $documentId = $db->table('documentos')->insertGetId([
                'proceso_admision_id' => $procesoId,
                'postulante_id'       => $postulanteId,
                'tipo'                => $fila['tipo'] ?? 'DOCUMENTO',
                'nombre_archivo'      => $fila['nombre_archivo'] ?? 'archivo',
                'url'                 => $fila['url'] ?? '',
                'estado'              => (bool) ($fila['estado'] ?? true),
                'created_at'          => $fila['created_at'] ?? now(),
                'updated_at'          => $fila['updated_at'] ?? now(),
            ]);

            if (isset($fila['id'])) {
                $this->registrarMapeo($db, 'documentos', (string) $fila['id'], (int) $documentId);
            }

            $importados++;
        }

        $this->line("  Documentos: {$importados} importados, {$omitidos} omitidos sin postulante.");
    }

    /** @param list<array<string, mixed>> $filas @param list<array<string, mixed>> $inscripciones */
    private function importarCorreosLegado(\Illuminate\Database\Connection $db, array $filas, array $inscripciones): void
    {
        if ($filas === []) {
            return;
        }

        $inscripcionByPerson = [];
        foreach ($inscripciones as $sourceInscription) {
            $sourcePersonId = (int) ($sourceInscription['postulante_id'] ?? 0);
            $destinationInscriptionId = $this->idMap['inscripcions'][(int) ($sourceInscription['id'] ?? 0)] ?? null;
            if ($sourcePersonId > 0 && $destinationInscriptionId !== null) {
                $inscripcionByPerson[$sourcePersonId] = $destinationInscriptionId;
            }
        }

        $importados = 0;
        $omitidos = 0;
        foreach ($filas as $fila) {
            $sourceId = (string) ($fila['id'] ?? '');
            if ($sourceId === '' || $this->buscarMapeo($db, 'correos', $sourceId) !== null) {
                continue;
            }

            $inscripcionId = $inscripcionByPerson[(int) ($fila['postulante_id'] ?? 0)] ?? null;
            if ($inscripcionId === null) {
                $omitidos++;
                continue;
            }

            $correoId = $db->table('correos')->insertGetId([
                'inscripcion_id' => $inscripcionId,
                'pre_inscripcion_id' => null,
                'tipo' => 'estado_legado',
                'detalle' => 'Estado de correo en origen: ' . (string) ($fila['estado'] ?? 'sin dato'),
                'created_at' => $fila['created_at'] ?? now(),
                'updated_at' => $fila['updated_at'] ?? now(),
            ]);
            $this->registrarMapeo($db, 'correos', $sourceId, (int) $correoId);
            $importados++;
        }

        $this->line("  Correos legacy: {$importados} registros vinculados a inscripciones, {$omitidos} sin enlace.");
    }

    /** @param list<array<string, mixed>> $filas */
    private function importarCorreos(\Illuminate\Database\Connection $db, array $filas): void
    {
        $importados = 0;
        $omitidos = 0;
        foreach ($filas as $fila) {
            $sourceId = (string) ($fila['id'] ?? '');
            if ($sourceId === '' || $this->buscarMapeo($db, 'correos', $sourceId) !== null) {
                continue;
            }

            $preInscripcionId = isset($fila['pre_inscripcion_id'])
                ? ($this->idMap['pre_inscripcions'][(int) $fila['pre_inscripcion_id']] ?? null)
                : null;
            $inscripcionId = isset($fila['inscripcion_id'])
                ? ($this->idMap['inscripcions'][(int) $fila['inscripcion_id']] ?? null)
                : null;
            if ($preInscripcionId === null && $inscripcionId === null) {
                $omitidos++;
                continue;
            }

            $id = $db->table('correos')->insertGetId([
                'pre_inscripcion_id' => $preInscripcionId,
                'inscripcion_id' => $inscripcionId,
                'tipo' => $fila['tipo'] ?? 'sin_tipo',
                'detalle' => $fila['detalle'] ?? '',
                'created_at' => $fila['created_at'] ?? now(),
                'updated_at' => $fila['updated_at'] ?? now(),
            ]);
            $this->registrarMapeo($db, 'correos', $sourceId, (int) $id);
            $importados++;
        }

        if ($filas !== []) {
            $this->line("  Correos: {$importados} importados, {$omitidos} sin preinscripción/inscripción mapeada.");
        }
    }

    /** @param list<array<string, mixed>> $filas */
    private function importarComision(\Illuminate\Database\Connection $db, int $procesoId, array $filas): void
    {
        $importados = 0;
        foreach ($filas as $fila) {
            $sourceId = (string) ($fila['id'] ?? '');
            if ($sourceId === '' || $this->buscarMapeo($db, 'comision_admisions', $sourceId) !== null) {
                continue;
            }

            $id = $db->table('comision_admisions')->insertGetId([
                'proceso_admision_id' => $procesoId,
                'facultad_id' => $this->idMap['facultads'][(int) ($fila['facultad_id'] ?? 0)] ?? null,
                'nombres' => $fila['nombres'] ?? '',
                'ap_paterno' => $fila['ap_paterno'] ?? '',
                'ap_materno' => $fila['ap_materno'] ?? '',
                'email' => $fila['email'] ?? '',
                'telefono' => $fila['telefono'] ?? null,
                'resumen_completo' => (bool) ($fila['resumen_completo'] ?? false),
                'estado' => (bool) ($fila['estado'] ?? true),
                'created_at' => $fila['created_at'] ?? now(),
                'updated_at' => $fila['updated_at'] ?? now(),
            ]);
            $this->registrarMapeo($db, 'comision_admisions', $sourceId, (int) $id);
            $importados++;
        }

        if ($filas !== []) {
            $this->line("  Comisión de admisión: {$importados} registros importados.");
        }
    }

    private function importarNotas(\Illuminate\Database\Connection $db, array $filas): void
    {
        if (empty($filas)) {
            return;
        }

        $importados = 0;
        $omitidos   = 0;

        foreach ($filas as $fila) {
            $inscripcionId = $this->idMap['inscripcions'][(int)($fila['inscripcion_id'] ?? 0)] ?? null;

            if ($inscripcionId === null) {
                $omitidos++;
                continue;
            }

            $existe = $db->table('notas')->where('inscripcion_id', $inscripcionId)->exists();
            if ($existe) {
                $omitidos++;
                continue;
            }

            $db->table('notas')->insert([
                'inscripcion_id' => $inscripcionId,
                'cv'             => $fila['cv'] ?? $fila['nota_cv'] ?? null,
                'entrevista'     => $fila['entrevista'] ?? $fila['nota_entrevista'] ?? null,
                'examen'         => $fila['examen'] ?? $fila['nota_examen'] ?? null,
                'final'          => $fila['final'] ?? $fila['nota_final'] ?? null,
                'created_at'     => $fila['created_at'] ?? now(),
                'updated_at'     => $fila['updated_at'] ?? now(),
            ]);

            $importados++;
        }

        $this->line("  Notas: {$importados} importadas, {$omitidos} omitidas (inscripción no encontrada o duplicada).");
    }

    // ------------------------------------------------------------------ //
    //  Remap de FKs del catálogo                                          //
    // ------------------------------------------------------------------ //

    /**
     * Traduce las FK de una fila del catálogo usando el idMap ya construido.
     *
     * @param array<string, mixed> $fila
     * @return array<string, mixed>
     */
    private function remapFksCatalogo(\Illuminate\Database\Connection $db, string $tabla, array $fila): array
    {
        return match ($tabla) {
            'provincias' => array_merge($fila, [
                'departamento_id' => $this->idMap['departamentos'][(int) ($fila['departamento_id'] ?? $fila['id_departamento'] ?? 0)]
                    ?? $fila['departamento_id'] ?? $fila['id_departamento'] ?? null,
            ]),
            'distritos'  => array_merge($fila, [
                'provincia_id' => $this->idMap['provincias'][(int) ($fila['provincia_id'] ?? $fila['id_provincia'] ?? 0)]
                    ?? $fila['provincia_id'] ?? $fila['id_provincia'] ?? null,
            ]),
            default      => $fila,
        };
    }

    /** @param array<string, mixed> $fila */
    private function resolverDistritoLegado(\Illuminate\Database\Connection $db, array $fila): ?int
    {
        $districtName = trim((string) ($fila['distrito'] ?? ''));
        $provinceName = trim((string) ($fila['provincia'] ?? ''));
        $departmentName = trim((string) ($fila['departamento'] ?? ''));

        if ($districtName === '' || $provinceName === '' || $departmentName === '') {
            return null;
        }

        $normalizeSql = static fn (string $column) => "LOWER(TRIM({$column}))";
        $districtId = $db->table('distritos')
            ->join('provincias', 'provincias.id', '=', 'distritos.provincia_id')
            ->join('departamentos', 'departamentos.id', '=', 'provincias.departamento_id')
            ->whereRaw($normalizeSql('distritos.nombre') . ' = ?', [mb_strtolower($districtName)])
            ->whereRaw($normalizeSql('provincias.nombre') . ' = ?', [mb_strtolower($provinceName)])
            ->whereRaw($normalizeSql('departamentos.nombre') . ' = ?', [mb_strtolower($departmentName)])
            ->value('distritos.id');

        return $districtId === null ? null : (int) $districtId;
    }

    /** @param array<string, mixed> $fila */
    private function observacionImportada(array $fila): ?string
    {
        $parts = [];
        $original = trim((string) ($fila['observacion'] ?? ''));
        if ($original !== '') {
            $parts[] = $original;
        }
        if (array_key_exists('expediente', $fila)) {
            $parts[] = 'Dato legado expediente=' . (string) $fila['expediente'];
        }

        $value = implode(' | ', $parts);
        return $value === '' ? null : mb_substr($value, 0, 255);
    }

    private function normalizarSexo(mixed $value): string
    {
        return match (mb_strtoupper(trim((string) $value))) {
            'M', 'MASCULINO', 'HOMBRE' => 'M',
            'F', 'FEMENINO', 'MUJER' => 'F',
            default => 'X',
        };
    }

    // ------------------------------------------------------------------ //
    //  Registro en mapeos_importacion                                     //
    // ------------------------------------------------------------------ //

    private function registrarMapeo(
        \Illuminate\Database\Connection $db,
        string $tabla,
        string $idOrigen,
        int $idDestino
    ): void {
        $db->table('mapeos_importacion')->insertOrIgnore([
            'fuente'        => $this->periodoActual,
            'tabla_origen'  => $tabla,
            'id_origen'     => $idOrigen,
            'tabla_destino' => $tabla,
            'id_destino'    => $idDestino,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function buscarMapeo(\Illuminate\Database\Connection $db, string $tabla, string $idOrigen): ?int
    {
        $id = $db->table('mapeos_importacion')
            ->where('fuente', $this->periodoActual)
            ->where('tabla_origen', $tabla)
            ->where('id_origen', $idOrigen)
            ->value('id_destino');

        return $id === null ? null : (int) $id;
    }

    // ------------------------------------------------------------------ //
    //  Reporte de conteos en dry-run                                      //
    // ------------------------------------------------------------------ //

    private function reportarConteos(array $data, string $periodo): void
    {
        $filas = [];
        $tablas = array_unique(array_merge(self::TABLAS_CATALOGO, self::TABLAS_OPERATIVAS, ['tabla_voucher']));

        foreach ($tablas as $tabla) {
            if (isset($data[$tabla]) && count($data[$tabla]) > 0) {
                $filas[] = [$tabla, count($data[$tabla])];
            }
        }

        $this->table(['Tabla', "Filas en {$periodo}"], $filas);
    }

    /** @param array<string, list<array<string, mixed>>> $data */
    private function reportarTransformaciones(array $data, string $periodo): void
    {
        if ($periodo === '2024-II') {
            $hasDni = count(array_filter($data['postulantes'] ?? [], static fn ($row) => trim((string) ($row['dni'] ?? '')) !== ''));
            $voucherLinks = count(array_filter($data['inscripcions'] ?? [], static fn ($row) => trim((string) ($row['cod_voucher'] ?? '')) !== ''));
            $this->table(
                ['Conversión prevista 2024-II', 'Cantidad/criterio'],
                [
                    ['DNI de persona disponible para mapear', $hasDni],
                    ['Voucher legacy enlazable por número', $voucherLinks],
                    ['Estado de inscripciones importado', '1 (activo), según confirmación del usuario'],
                    ['Validación digital', 'Se conserva desde validado'],
                    ['Expediente legado', 'Se conserva en observación; no se interpreta como booleano'],
                    ['Archivos legacy', count($data['documentos'] ?? []) . ' rutas documentales; no incluye los binarios'],
                    ['Correos legacy', count($data['correos'] ?? []) . ' filas; se vinculan con la inscripción del postulante'],
                ]
            );
            $this->comment('El modo dry-run solo informa; no escribe ni en la base origen ni en db_sistema_admision.');
            return;
        }

        $this->line('La fuente moderna conserva sus estados, calificaciones y referencias; se traducen las FK al catálogo unificado.');
    }

    private function reportarEstadoDestino(string $periodo, array $data): bool
    {
        try {
            $db = DB::connection(self::DESTINO);
            $process = $db->table('procesos_admision')->where('codigo', $periodo)->first(['id', 'estado']);
            if (! $process) {
                $this->warn("Destino: el proceso {$periodo} todavía no existe; se crearía al aplicar.");
                return true;
            }

            $rows = [['proceso_admision', $process->estado]];
            foreach (['programas_procesos', 'pre_inscripcions', 'vouchers', 'inscripcions', 'documentos', 'comision_admisions'] as $table) {
                if (! $db->getSchemaBuilder()->hasTable($table)) {
                    continue;
                }
                $rows[] = [$table, $db->table($table)->where('proceso_admision_id', $process->id)->count()];
            }
            $rows[] = ['mapeos de importación existentes', $db->table('mapeos_importacion')
                ->where('fuente', $periodo)->count()];

            $this->newLine();
            $this->table(['Destino para ' . $periodo, 'Estado/filas actuales'], $rows);

            if ($periodo === '2024-II') {
                $geoCache = [];
                $unmatched = 0;
                foreach ($data['postulantes'] ?? [] as $person) {
                    $key = implode('|', array_map(
                        static fn ($part) => mb_strtolower(trim((string) $part)),
                        [$person['departamento'] ?? '', $person['provincia'] ?? '', $person['distrito'] ?? '']
                    ));
                    if (! array_key_exists($key, $geoCache)) {
                        $geoCache[$key] = $this->resolverDistritoLegado($db, $person);
                    }
                    if ($geoCache[$key] === null) {
                        $unmatched++;
                    }
                }
                $this->line('Ubicaciones personales 2024-II que no encuentran distrito equivalente en destino: ' . $unmatched);
                if ($unmatched > 0) {
                    return false;
                }
            }

            return true;
        } catch (Throwable $exception) {
            $this->warn('No se pudo consultar db_sistema_admision en este dry-run: ' . $exception->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ //
    //  Parser de SQL (basado en AuditarRelacionesRespaldos)               //
    // ------------------------------------------------------------------ //

    /**
     * @param list<string> $tablas
     * @return array<string, list<array<string, string|null>>>
     */
    private function parsearSQL(string $sql, array $tablas): array
    {
        $schemas = [];
        if (preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE/is', $sql, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $cols);
                $schemas[strtolower($match[1])] = array_map('strtolower', $cols[1]);
            }
        }

        $result = [];
        if (! preg_match_all('/INSERT\s+INTO\s+`([^`]+)`/i', $sql, $inserts, PREG_OFFSET_CAPTURE)) {
            return $result;
        }

        foreach ($inserts[0] as $index => [$token, $offset]) {
            $tabla = strtolower($inserts[1][$index][0]);
            if (! in_array($tabla, $tablas, true)) {
                continue;
            }

            $after        = $offset + strlen($token);
            $valuesOffset = stripos($sql, 'VALUES', $after);
            if ($valuesOffset === false) {
                continue;
            }

            $header  = substr($sql, $after, $valuesOffset - $after);
            $columns = $this->sqlInsertColumns($header) ?: ($schemas[$tabla] ?? []);
            if ($columns === []) {
                continue;
            }

            foreach ($this->sqlReadTuples($sql, $valuesOffset + 6) as $tuple) {
                $values = array_map($this->sqlDecode(...), $this->sqlSplitFields($tuple));
                if (count($values) === count($columns)) {
                    $result[$tabla][] = array_combine($columns, $values);
                }
            }
        }

        return $result;
    }

    /** @return list<string> */
    private function sqlInsertColumns(string $header): array
    {
        if (! preg_match('/\((.*?)\)\s*$/s', trim($header), $m)) {
            return [];
        }
        preg_match_all('/`([^`]+)`/', $m[1], $cols);
        return array_map('strtolower', $cols[1]);
    }

    /** @return list<string> */
    private function sqlReadTuples(string $sql, int $offset): array
    {
        $tuples = [];
        $depth  = 0;
        $quote  = null;
        $esc    = false;
        $start  = null;

        for ($i = $offset, $len = strlen($sql); $i < $len; $i++) {
            $c = $sql[$i];
            if ($quote !== null) {
                if ($esc)          { $esc = false; }
                elseif ($c === '\\')  { $esc = true; }
                elseif ($c === $quote) {
                    if (($sql[$i + 1] ?? null) === $quote) { $i++; }
                    else                                    { $quote = null; }
                }
                continue;
            }
            if (in_array($c, ["'", '"', '`'], true)) { $quote = $c; }
            elseif ($c === '(')    { if ($depth++ === 0) { $start = $i + 1; } }
            elseif ($c === ')')    { if (--$depth === 0 && $start !== null) { $tuples[] = substr($sql, $start, $i - $start); $start = null; } }
            elseif ($c === ';' && $depth === 0) { break; }
        }

        return $tuples;
    }

    /** @return list<string> */
    private function sqlSplitFields(string $tuple): array
    {
        $fields = [];
        $start  = 0;
        $depth  = 0;
        $quote  = null;
        $esc    = false;

        for ($i = 0, $len = strlen($tuple); $i < $len; $i++) {
            $c = $tuple[$i];
            if ($quote !== null) {
                if ($esc)          { $esc = false; }
                elseif ($c === '\\')  { $esc = true; }
                elseif ($c === $quote) {
                    if (($tuple[$i + 1] ?? null) === $quote) { $i++; }
                    else                                      { $quote = null; }
                }
                continue;
            }
            if (in_array($c, ["'", '"', '`'], true)) { $quote = $c; }
            elseif ($c === '(')    { $depth++; }
            elseif ($c === ')')    { $depth--; }
            elseif ($c === ',' && $depth === 0) { $fields[] = trim(substr($tuple, $start, $i - $start)); $start = $i + 1; }
        }

        $fields[] = trim(substr($tuple, $start));
        return $fields;
    }

    private function sqlDecode(string $v): ?string
    {
        $v = trim($v);
        if (strtoupper($v) === 'NULL') {
            return null;
        }
        if (strlen($v) >= 2 && $v[0] === "'" && substr($v, -1) === "'") {
            return str_replace(
                ["\\0", "\\b", "\\n", "\\r", "\\t", "\\Z", "\\'", '\\"', "''", '\\\\'],
                ["\0",  "\b",  "\n",  "\r",  "\t",  "\x1a", "'",   '"',  "'",  '\\'],
                substr($v, 1, -1)
            );
        }
        return $v;
    }
}
