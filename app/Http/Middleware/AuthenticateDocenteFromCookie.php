<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDocenteFromCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token_docente');
        if (!$token) {
           
            return response()->json(['error' => 'Token no encontrado'], 401);
        }

        try {
            $user = auth('docente')->setToken($token)->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Token inválido'], 401);
            }
            auth()->guard('docente')->setUser($user);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
