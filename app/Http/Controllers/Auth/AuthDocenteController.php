<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Docente;
use App\Models\RefreshToken;
use App\Services\TokenCookieService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthDocenteController extends BaseController
{
    public function __construct(
        protected TokenCookieService $cookieService
    ) {
    }

    public function login(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $docente = Docente::where('email', $credentials['email'])->first();

            if (!$docente || !Hash::check($credentials['password'], $docente->password)) {
                return $this->errorResponse('Credenciales incorrectas', 401);
            }

            if ($docente->estado == false) {
                return $this->errorResponse('Usuario inactivo, contacte al administrador', 403);
            }

            // Generar token de acceso (JWT)
            $token = Auth::guard('docente')->attempt($credentials);

            if (!$token) {
                return $this->errorResponse('Error al generar el token de acceso', 500);
            }

            // Crear el refresh token JWT
            $refreshToken = JWTAuth::fromUser($docente, ['exp' => now()->addDays(30)->timestamp]);

            // Guardar el refresh token en la base de datos
            $docente->refreshTokens()->updateOrCreate(
                [],
                [
                    'token' => $refreshToken,
                    'expires_at' => now()->addDays(30),
                    'last_used_at' => now(),
                ]
            );

            // Cookies HttpOnly y Secure
            $accessCookie = $this->cookieService->makeAccessCookie($token, 'token_docente');
            $refreshCookie = $this->cookieService->makeRefreshCookie($refreshToken, 'refresh_token_docente');

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'docente' => $docente
            ])->withCookie($accessCookie)
                ->withCookie($refreshCookie);
        }, 'Error en el login de docente');
    }

    public function refreshDocente(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $token = $request->cookie('refresh_token_docente');
            if (!$token) {
                return $this->errorResponse('Refresh token no proporcionado', 401);
            }

            $refreshToken = RefreshToken::where('token', $token)
                ->where('expires_at', '>', now())
                ->whereHasMorph('authenticatable', [Docente::class])
                ->first();

            if (!$refreshToken) {
                return $this->errorResponse('Token de refresco inválido o expirado', 401);
            }

            $docente = $refreshToken->authenticatable;
            $accessToken = JWTAuth::fromUser($docente);
            $newRefreshToken = JWTAuth::fromUser($docente, ['exp' => now()->addDays(30)->timestamp]);

            $refreshToken->update([
                'token' => $newRefreshToken,
                'expires_at' => now()->addDays(30),
                'last_used_at' => now(),
            ]);

            $accessCookie = $this->cookieService->makeAccessCookie($accessToken, 'token_docente');
            $refreshCookie = $this->cookieService->makeRefreshCookie($newRefreshToken, 'refresh_token_docente');

            return response()->json(['message' => 'Token renovado'])
                ->withCookie($accessCookie)
                ->withCookie($refreshCookie);
        }, 'Error al refrescar token');
    }

    public function logout(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            try {
                $token = $request->cookie('token_docente');

                if ($token) {
                    JWTAuth::setToken($token)->invalidate();
                }

                $docente = Auth::guard('docente')->user();

                if ($docente) {
                    $docente->refreshTokens()->delete();
                }

                $forgetAccess = $this->cookieService->forgetAccessCookie('token_docente');
                $forgetRefresh = $this->cookieService->forgetRefreshCookie('refresh_token_docente');

                return response()->json(['message' => 'Sesión cerrada correctamente'])
                    ->withCookie($forgetAccess)
                    ->withCookie($forgetRefresh);
            } catch (Exception $e) {
                // Even if token invalidation fails, we clear cookies
                $forgetAccess = $this->cookieService->forgetAccessCookie('token_docente');
                $forgetRefresh = $this->cookieService->forgetRefreshCookie('refresh_token_docente');

                return response()->json(['message' => 'Sesión cerrada'])
                    ->withCookie($forgetAccess)
                    ->withCookie($forgetRefresh);
            }
        }, 'Error al cerrar sesión');
    }

    public function checkAuthDocente(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            try {
                $token = $request->cookie('token_docente');
                if (!$token) {
                    return response()->json(['authenticated' => false]);
                }

                $docente = JWTAuth::setToken($token)->authenticate();
                if (!$docente) {
                    return response()->json(['authenticated' => false]);
                }

                // Update last used at for refresh token to track activity
                $refreshToken = $docente->refreshTokens()->first();
                if ($refreshToken) {
                    $refreshToken->update(['last_used_at' => now()]);
                }

                return response()->json(['authenticated' => true, 'docente' => $docente]);
            } catch (TokenExpiredException) {
                return response()->json(['authenticated' => false, 'error' => 'El token ha expirado']);
            } catch (Exception) {
                return response()->json(['authenticated' => false]);
            }
        }, 'Error al verificar autenticación');
    }
}
