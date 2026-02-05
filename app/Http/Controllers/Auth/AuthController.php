<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $user = $request->user();

            if (!$user) {
                return $this->unauthorized('Usuario no autenticado');
            }

            return $this->successResponse([
                'authenticated' => true,
                'user' => $user,
                'role' => $user->roles()->pluck('slug')->first(),
            ]);
        }, 'Error al obtener el perfil');
    }

    /**
     * Login with email and password (Cypress/Testing)
     */
    public function loginCypress(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $resultado = $this->authService->loginWithCredentials(
                $credentials['email'],
                $credentials['password']
            );

            if (!$resultado['success']) {
                return $this->errorResponse($resultado['message'], 404);
            }

            $this->logActivity('Login con credenciales', null, [
                'email' => $credentials['email'],
            ]);

            return $this->successResponse($resultado);
        }, 'Error en el login');
    }

    /**
     * Login with Google OAuth
     */
    public function googleLogin(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'credential' => 'required|string',
            ]);

            $resultado = $this->authService->loginWithGoogle($request->credential);

            if (!$resultado['success']) {
                return $this->errorResponse($resultado['message'], 401);
            }

            $response = $this->successResponse($resultado);
            

            if (isset($resultado['cookies'])) {
                foreach ($resultado['cookies'] as $cookie) {
                    $response->withCookie($cookie);
                }
            }

            return $response;
        }, 'Error en el login con Google');
    }

    /**
     * Login for RPA with extended token expiration
     */
    public function loginRPA(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $resultado = $this->authService->loginWithCustomExpiration(
                $credentials['email'],
                $credentials['password'],
                60 * 24 * 180 // 6 months
            );

            if (!$resultado['success']) {
                return $this->errorResponse($resultado['message'], 401);
            }

            $this->logActivity('Login RPA', null, [
                'email' => $credentials['email'],
            ]);

            return $this->successResponse($resultado);
        }, 'Error en el login RPA');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $token = $request->bearerToken();
            $user = $request->user('api');
            $resultado = $this->authService->logout($token, $user);

            $response = $this->successResponse(['message' => 'Sesión cerrada correctamente']);

            if (isset($resultado['cookies'])) {
                foreach ($resultado['cookies'] as $cookie) {
                    $response->withCookie($cookie);
                }
            }

            return $response;
        }, 'Error al cerrar sesión');
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return $this->errorResponse('Token de refresco no encontrado', 401);
            }

            $resultado = $this->authService->refreshToken($refreshToken);

            if (!$resultado['success']) {
                return $this->errorResponse($resultado['message'], 401);
            }

            $response = $this->successResponse($resultado);

            if (isset($resultado['cookies'])) {
                foreach ($resultado['cookies'] as $cookie) {
                    $response->withCookie($cookie);
                }
            }

            return $response;
        }, 'Error al refrescar el token');
    }

    // ==================== User CRUD Methods ====================
    // Note: These should ideally be in a separate UserController

    /**
     * Get all users
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $users = User::all();
            return $this->successResponse($users);
        }, 'Error al obtener usuarios');
    }

    /**
     * Get user by ID
     */
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $user = User::findOrFail($id);
            return $this->successResponse($user);
        }, 'Error al obtener el usuario');
    }

    /**
     * Create new user
     */
    public function store(RegisterRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'password' => $request->password ? \Hash::make($request->password) : null,
            ]);

            $this->logActivity('Usuario creado', null, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $this->successResponse($user, 'Usuario creado exitosamente', 201);
        }, 'Error al crear el usuario');
    }

    /**
     * Update user
     */
    public function update(RegisterRequest $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = User::findOrFail($id);

            $dataToUpdate = $request->only(['name', 'email', 'google_id']);

            if ($request->filled('password')) {
                $dataToUpdate['password'] = \Hash::make($request->password);
            }

            $user->update($dataToUpdate);

            $this->logActivity('Usuario actualizado', null, [
                'user_id' => $user->id,
            ]);

            return $this->successResponse($user, 'Usuario actualizado exitosamente');
        }, 'Error al actualizar el usuario');
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            User::destroy($id);

            $this->logActivity('Usuario eliminado', null, [
                'user_id' => $id,
            ]);

            return $this->successResponse(null, 'Usuario eliminado exitosamente', 204);
        }, 'Error al eliminar el usuario');
    }
    /**
     * Check if user is authenticated
     */
    public function checkAuth(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $user = $request->user('api');
            if (!$user) {
          
                return $this->successResponse(['authenticated' => false]);
            }
          
            return $this->successResponse([
                'authenticated' => true,
                'user' => $user,
                'role' => $user->roles()->pluck('slug')->first(),
            ]);
        }, 'Error al verificar autenticación');
    }
}
