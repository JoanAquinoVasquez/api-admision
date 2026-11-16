<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProcesoAdmisionController extends BaseController
{
    public function index(Request $request)
    {
        $db = DB::connection('mysql_unificada');
        $userId = (int) $request->user()->getAuthIdentifier();
        $isSuperAdmin = $this->isSuperAdmin($db, $userId);

        $query = $db->table('procesos_admision as p')->select([
            'p.id', 'p.codigo', 'p.nombre', 'p.estado', 'p.fecha_inicio', 'p.fecha_fin',
        ])->orderByDesc('p.codigo');

        if (! $isSuperAdmin) {
            $query->whereExists(function ($subquery) use ($db, $userId) {
                $subquery->selectRaw('1')
                    ->from('proceso_admision_user_roles as pur')
                    ->join('roles as r', 'r.id', '=', 'pur.role_id')
                    ->whereColumn('pur.proceso_admision_id', 'p.id')
                    ->where('pur.user_id', $userId)
                    ->whereIn('r.slug', ['admin', 'comision']);
            });
        }

        $processes = $query->get()->map(function ($process) use ($db, $userId, $isSuperAdmin) {
            $roles = $isSuperAdmin
                ? ['super-admin']
                : $db->table('proceso_admision_user_roles as pur')
                    ->join('roles as r', 'r.id', '=', 'pur.role_id')
                    ->where('pur.proceso_admision_id', $process->id)
                    ->where('pur.user_id', $userId)
                    ->pluck('r.slug')->all();

            return [
                'id' => (int) $process->id,
                'codigo' => $process->codigo,
                'nombre' => $process->nombre,
                'estado' => $process->estado,
                'fecha_inicio' => $process->fecha_inicio,
                'fecha_fin' => $process->fecha_fin,
                'roles' => $roles,
                'solo_lectura' => $process->estado === 'cerrado',
                'puede_escribir' => $isSuperAdmin
                    ? $process->estado !== 'cerrado'
                    : in_array('admin', $roles, true) && $process->estado !== 'cerrado',
            ];
        });

        return $this->successResponse([
            'procesos' => $processes,
            'sistema_unificado' => DB::getDefaultConnection() === 'mysql_unificada',
        ]);
    }

    public function show(Request $request, string $codigo)
    {
        $db = DB::connection('mysql_unificada');
        $userId = (int) $request->user()->getAuthIdentifier();
        $process = $db->table('procesos_admision')->where('codigo', $codigo)->first();

        if (! $process) {
            return $this->errorResponse('Proceso de admisión no encontrado.', 404);
        }

        $isSuperAdmin = $this->isSuperAdmin($db, $userId);
        $roles = $isSuperAdmin
            ? ['super-admin']
            : $db->table('proceso_admision_user_roles as pur')
                ->join('roles as r', 'r.id', '=', 'pur.role_id')
                ->where('pur.proceso_admision_id', $process->id)
                ->where('pur.user_id', $userId)
                ->pluck('r.slug')->all();

        if (! $isSuperAdmin && array_intersect($roles, ['admin', 'comision']) === []) {
            return $this->errorResponse('No tienes acceso a este proceso.', 403);
        }

        return $this->successResponse([
            'id' => (int) $process->id,
            'codigo' => $process->codigo,
            'nombre' => $process->nombre,
            'estado' => $process->estado,
            'fecha_inicio' => $process->fecha_inicio,
            'fecha_fin' => $process->fecha_fin,
            'roles' => $roles,
            'solo_lectura' => $process->estado === 'cerrado',
            'puede_escribir' => $isSuperAdmin
                ? $process->estado !== 'cerrado'
                : in_array('admin', $roles, true) && $process->estado !== 'cerrado',
        ]);
    }

    public function sincronizarRolesUsuario(Request $request, string $codigo, int $userId)
    {
        $validated = $request->validate([
            'roles' => ['present', 'array'],
            'roles.*' => ['string', Rule::in(['admin', 'comision'])],
        ]);

        $db = DB::connection('mysql_unificada');
        $process = $db->table('procesos_admision')->where('codigo', $codigo)->first(['id']);
        if (! $process) {
            return $this->errorResponse('Proceso de admisión no encontrado.', 404);
        }
        if (! $db->table('users')->where('id', $userId)->exists()) {
            return $this->errorResponse('Usuario no encontrado.', 404);
        }

        $roleIds = $db->table('roles')->whereIn('slug', array_unique($validated['roles']))->pluck('id', 'slug');
        $db->transaction(function () use ($db, $process, $userId, $validated, $roleIds) {
            $db->table('proceso_admision_user_roles')
                ->where('proceso_admision_id', $process->id)
                ->where('user_id', $userId)
                ->delete();

            foreach (array_unique($validated['roles']) as $slug) {
                if (! isset($roleIds[$slug])) {
                    continue;
                }
                $db->table('proceso_admision_user_roles')->insert([
                    'proceso_admision_id' => $process->id,
                    'user_id' => $userId,
                    'role_id' => $roleIds[$slug],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return $this->successResponse([
            'proceso' => $codigo,
            'user_id' => $userId,
            'roles' => array_values(array_unique($validated['roles'])),
        ], 'Accesos al proceso actualizados.');
    }

    private function isSuperAdmin($db, int $userId): bool
    {
        return $db->table('role_user as ru')
            ->join('roles as r', 'r.id', '=', 'ru.role_id')
            ->where('ru.user_id', $userId)
            ->where('r.slug', 'super-admin')
            ->exists();
    }
}
