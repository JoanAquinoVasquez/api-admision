<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Middleware\AuthenticateDocenteFromCookie;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '', // Quitamos el prefijo 'api'
    )
    // ->withBroadcasting(
    //     __DIR__ . '/../routes/channels.php',
    //     ['prefix' => 'api', 'middleware' => ['api', 'auth:api']],
    // )
    ->withMiddleware(function (Middleware $middleware) {
        // API pura: nunca redirigir a 'login', lanzar AuthenticationException directamente
        $middleware->redirectGuestsTo(fn() => null);

        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: [
            'token',
            'refresh_token',
        ]);

        $middleware->appendToGroup('web', [
            EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Http\Middleware\HandleCors::class, // Agregado para CORS
        ]);

        $middleware->appendToGroup('api', [
            \Illuminate\Http\Middleware\HandleCors::class, // Agregado para CORS
            EncryptCookies::class,
            \App\Http\Middleware\AttachTokenFromCookie::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'active' => \App\Http\Middleware\ActiveUserMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class, // Roles
            'auth.docente.cookie' => \App\Http\Middleware\AuthenticateDocenteFromCookie::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {

        // 401 - No autenticado
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        });

        // 401 - Token JWT expirado
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'El token ha expirado',
            ], 401);
        });

        // 401 - Token JWT inválido
        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
            ], 401);
        });

        // 401 - Error JWT genérico
        $exceptions->render(function (JWTException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage(),
            ], 401);
        });

        // 403 - Sin permisos
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        });

        // 404 - Modelo no encontrado
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ], 404);
        });

        // 429 - Too Many Requests (Rate Limit)
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Por favor, intenta más tarde.',
            ], 429);
        });

        // 500 - Cualquier otro error no manejado (red de seguridad)
        $exceptions->render(function (\Throwable $e, Request $request) {
            Log::error('Error no manejado: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        });

    })->create();
