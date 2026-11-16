<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MigrarUsuariosAdmision extends Command
{
    protected $signature = 'admision:usuarios:migrar {--aplicar : Copia las cuentas y roles a mysql_unificada}';

    protected $description = 'Simula o copia cuentas y roles del sistema actual a la base unificada, sin sesiones activas.';

    private const ROLES_POR_PROCESO = ['admin', 'comision'];

    private const COLUMNAS_USUARIO = [
        'id', 'name', 'email', 'email_verified_at', 'password', 'google_id',
        'profile_picture', 'estado', 'created_at', 'updated_at',
    ];

    public function handle(): int
    {
        $source = DB::connection('mysql');
        $target = DB::connection('mysql_unificada');

        foreach (['users', 'roles', 'role_user'] as $table) {
            if (! $source->getSchemaBuilder()->hasTable($table) || ! $target->getSchemaBuilder()->hasTable($table)) {
                $this->error("Falta la tabla requerida '{$table}' en una de las conexiones.");
                return self::FAILURE;
            }
        }

        $users = $source->table('users')->orderBy('id')->get(self::COLUMNAS_USUARIO);
        $roles = $source->table('roles')->orderBy('id')->get(['id', 'nombre', 'slug']);
        $assignments = $source->table('role_user')->orderBy('id')->get(['user_id', 'role_id']);
        $process = $target->table('procesos_admision')->where('codigo', '2026-I')->first(['id']);

        if (! $process) {
            $this->error('No existe el proceso 2026-I en mysql_unificada. No se realizaron cambios.');
            return self::FAILURE;
        }

        $roleById = $roles->keyBy('id');
        $roleSlugsInTarget = $target->table('roles')->pluck('slug')->all();
        $targetUserIds = array_fill_keys($target->table('users')->pluck('id')->all(), true);
        $targetEmails = array_fill_keys(
            $target->table('users')->pluck('email')->map(fn ($email) => strtolower((string) $email))->all(),
            true
        );

        $idConflicts = 0;
        $emailConflicts = 0;
        foreach ($users as $user) {
            $idConflicts += isset($targetUserIds[$user->id]) ? 1 : 0;
            $emailConflicts += isset($targetEmails[strtolower((string) $user->email)]) ? 1 : 0;
        }

        $missingRoles = $roles->filter(fn ($role) => ! in_array($role->slug, $roleSlugsInTarget, true))->count();
        $scopedAssignments = 0;
        $globalAssignments = 0;
        foreach ($assignments as $assignment) {
            $slug = $roleById->get($assignment->role_id)?->slug;
            if (in_array($slug, self::ROLES_POR_PROCESO, true)) {
                $scopedAssignments++;
            } else {
                $globalAssignments++;
            }
        }

        $this->table(
            ['Elemento', 'Origen', 'Acción prevista'],
            [
                ['Cuentas', $users->count(), 'Copiar conservando IDs y hashes de contraseña'],
                ['Roles', $roles->count(), "Copiar los que falten por slug ({$missingRoles})"],
                ['Asignaciones admin/comision', $scopedAssignments, 'Asignar al proceso 2026-I'],
                ['Otras asignaciones de rol', $globalAssignments, 'Conservar como roles globales'],
                ['Conflictos de ID en destino', $idConflicts, 'Debe ser 0 para aplicar'],
                ['Conflictos de correo en destino', $emailConflicts, 'Debe ser 0 para aplicar'],
                ['Tokens y sesiones', 'No se copian', 'El inicio de sesión renovará las sesiones'],
            ]
        );

        if (! $this->option('aplicar')) {
            $this->comment('Simulación terminada. No se modificó ninguna base de datos.');
            return self::SUCCESS;
        }

        if ($idConflicts > 0 || $emailConflicts > 0) {
            $this->error('Hay cuentas que colisionan con datos del destino. Resuelve el conflicto antes de aplicar.');
            return self::FAILURE;
        }

        try {
            $target->transaction(function () use ($target, $users, $roles, $assignments, $process): void {
                foreach ($roles as $role) {
                    $target->table('roles')->updateOrInsert(
                        ['slug' => $role->slug],
                        ['nombre' => $role->nombre, 'updated_at' => now(), 'created_at' => now()]
                    );
                }

                foreach ($users as $user) {
                    $row = (array) $user;
                    $row['remember_token'] = null;
                    $target->table('users')->insert($row);
                }

                $targetRoleIds = $target->table('roles')->pluck('id', 'slug');
                $sourceRoleSlugs = $roles->keyBy('id');
                foreach ($assignments as $assignment) {
                    $slug = $sourceRoleSlugs->get($assignment->role_id)?->slug;
                    $targetRoleId = $targetRoleIds[$slug] ?? null;
                    if (! $targetRoleId) {
                        throw new RuntimeException("No se encontró el rol '{$slug}' en destino.");
                    }

                    if (in_array($slug, self::ROLES_POR_PROCESO, true)) {
                        $target->table('proceso_admision_user_roles')->updateOrInsert(
                            [
                                'proceso_admision_id' => $process->id,
                                'user_id' => $assignment->user_id,
                                'role_id' => $targetRoleId,
                            ],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                        continue;
                    }

                    $target->table('role_user')->insert([
                        'user_id' => $assignment->user_id,
                        'role_id' => $targetRoleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->error('La copia se revirtió por un error: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Cuentas y roles copiados a db_sistema_admision. La base origen no fue modificada.');
        return self::SUCCESS;
    }
}
