<?php

namespace App\Http\Middleware;

use App\Models\ProcesoAdmision;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProcessAccessMiddleware
{
    /**
     * Require an explicit process assignment for scoped roles.
     * Usage: process.access:admin|comision or process.access:admin|comision,write
     */
    public function handle(Request $request, Closure $next, string $roles, string $operation = 'auto'): Response
    {
        // The legacy 2026 connection has no process-scoped schema. Enforcement activates at cutover.
        if (DB::getDefaultConnection() !== 'mysql_unificada') {
            return $next($request);
        }

        if (str_starts_with($request->path(), 'procesos-admision') || str_starts_with($request->path(), 'users')) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['authenticated' => false, 'error' => 'No autenticado'], 401);
        }

        $processId = $request->input('proceso_admision_id')
            ?? $request->header('X-Admission-Process')
            ?? $request->route('proceso_admision_id');

        if (! is_numeric($processId)) {
            return response()->json([
                'error' => 'Debes indicar el proceso de admisión para esta operación.',
                'field' => 'proceso_admision_id',
            ], 422);
        }

        $process = ProcesoAdmision::query()->find($processId);
        if (! $process) {
            return response()->json(['error' => 'Proceso de admisión no encontrado.'], 404);
        }

        $allowedRoles = explode('|', $roles);
        // super-admin is a global role; it does not need a per-process assignment.
        if (! $user->hasAnyRole(['super-admin'])
            && ! $user->hasRoleForProcess((int) $processId, $allowedRoles)) {
            return response()->json(['authenticated' => true, 'error' => 'No autorizado para este proceso.'], 403);
        }

        $isWrite = $operation === 'write'
            || ($operation === 'auto' && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true));
        if ($isWrite && $process->esSoloLectura()) {
            return response()->json(['error' => 'El proceso está cerrado y solo permite consultas.'], 403);
        }

        $request->attributes->set('proceso_admision', $process);

        return $next($request);
    }
}
