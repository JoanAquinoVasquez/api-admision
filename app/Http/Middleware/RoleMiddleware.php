<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        if ($user->hasAnyRole($rolesArray)) {
            return $next($request);
        }

        return response()->json([
            'authenticated' => true,
            'error' => 'No autorizado',
        ], 403);
    }
}
