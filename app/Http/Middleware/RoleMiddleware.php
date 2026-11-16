<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado
        if (! $user) {
            return response()->json([
                'authenticated' => false,
                'error' => 'Token inválido o usuario no autenticado',
            ], 401);
        }

        $rolesArray = explode('|', $roles);
        $scopedRoles = array_values(array_intersect($rolesArray, ['admin', 'comision']));

        // Keep the current legacy connection behavior until the application is cut over.
        if (DB::getDefaultConnection() !== 'mysql_unificada' && $user->hasAnyRole($rolesArray)) {
            return $next($request);
        }

        $globalRoles = array_values(array_diff($rolesArray, ['admin', 'comision']));
        if ($globalRoles !== [] && $user->hasAnyRole($globalRoles)) {
            if ($scopedRoles === [] || $request->routeIs('procesos-admision.index')) {
                return $next($request);
            }
        }

        if ($scopedRoles !== []) {
            $processId = $request->input('proceso_admision_id')
                ?? $request->header('X-Admission-Process')
                ?? $request->route('proceso_admision_id');

            if (DB::getDefaultConnection() === 'mysql_unificada'
                && ! is_numeric($processId)
                && ! $request->routeIs('procesos-admision.index')) {
                return response()->json([
                    'authenticated' => true,
                    'error' => 'Selecciona un proceso de admisión.',
                    'field' => 'proceso_admision_id',
                ], 422);
            }

            if (DB::getDefaultConnection() === 'mysql_unificada' && is_numeric($processId)) {
                $process = DB::connection('mysql_unificada')->table('procesos_admision')->find((int) $processId);
                if (! $process) {
                    return response()->json(['error' => 'Proceso de admisión no encontrado.'], 404);
                }
                $request->attributes->set('proceso_admision', $process);
            }

            if (is_numeric($processId) && $user->hasRoleForProcess((int) $processId, $scopedRoles)) {
                return $next($request);
            }

            if ($globalRoles !== [] && $user->hasAnyRole($globalRoles) && is_numeric($processId)) {
                return $next($request);
            }

            if ($request->routeIs('procesos-admision.index') && $user->hasAnyProcessRole($scopedRoles)) {
                return $next($request);
            }

            if (! is_numeric($processId) && ! $request->routeIs('procesos-admision.index')) {
                return response()->json([
                    'authenticated' => true,
                    'error' => 'Selecciona un proceso de admisión.',
                    'field' => 'proceso_admision_id',
                ], 422);
            }
        }

        return response()->json([
            'authenticated' => true,
            'error' => 'No autorizado',
        ], 403);
    }
}
