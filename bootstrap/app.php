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
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'authenticated' => false,
                'error' => 'No autenticado'
            ], 401);
        });

        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            return response()->json([
                'authenticated' => false,
                'error' => 'El token ha expirado'
            ], 401);
        });

        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            return response()->json([
                'authenticated' => false,
                'error' => 'Token inválido'
            ], 401);
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            return response()->json([
                'authenticated' => false,
                'error' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes permisos para realizar esta acción'
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error' => 'Recurso no encontrado'
            ], 404);
        });
    })->create();
