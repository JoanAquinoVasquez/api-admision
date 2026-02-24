<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Google_Client;

class AuthService
{
    /**
     * Login with email and password credentials
     */
    public function __construct(
        protected TokenCookieService $cookieService
    ) {
    }

    /**
     * Login with email and password credentials
     */
    public function loginWithCredentials(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'El correo o la contraseña son incorrectos',
            ];
        }

        if ($user->estado == false) {
            return [
                'success' => false,
                'message' => 'Usuario inactivo, contacte al administrador',
            ];
        }

        $tokens = $this->generateTokensForUser($user);
        $this->logUserActivity($user, 'El usuario inició sesión');

        return [
            'success' => true,
            'message' => 'Autenticado con éxito',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
            ],
            'token' => $tokens['access_token'],
            'cookies' => $tokens['cookies'],
        ];
    }

    /**
     * Login with Google token
     */
    public function loginWithGoogle(string $googleToken): array
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($googleToken);

        if (!$payload) {
            return [
                'success' => false,
                'message' => 'Token de Google inválido',
            ];
        }
        $googleId = $payload['sub'];
        $name = $payload['name'];
        $email = $payload['email'];
        $picture = $payload['picture'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado',
            ];
        }

        if ($user->estado == false) {
            return [
                'success' => false,
                'message' => 'Usuario inactivo, contacte al administrador',
            ];
        }

        // Update user info
        $user->update([
            'name' => $name,
            'google_id' => $googleId,
            'email' => $email,
            'profile_picture' => $picture,
        ]);

        $tokens = $this->generateTokensForUser($user);
        $this->logUserActivity($user, 'El usuario inició sesión');

        return [
            'success' => true,
            'message' => 'Autenticado con éxito',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
            ],
            'cookies' => $tokens['cookies'],
        ];
    }

    /**
     * Login for RPA with custom expiration (6 months)
     */
    public function loginWithCustomExpiration(string $email, string $password, int $expirationMonths = 6): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ];
        }

        $customClaims = [
            'exp' => now()->addMonths($expirationMonths)->timestamp,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return [
            'success' => true,
            'token' => $token,
        ];
    }

    /**
     * Logout user and invalidate tokens
     */
    public function logout(?string $token, ?User $user): array
    {
        if ($user) {
            // Delete refresh token from database
            RefreshToken::where('authenticatable_type', 'User')
                ->where('authenticatable_id', $user->id)
                ->delete();

            $this->logUserActivity($user, 'El usuario cerró sesión');
        }

        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (\Exception $e) {
                // Token already invalid or expired
            }
        }

        $cookies = [
            'access' => $this->cookieService->forgetAccessCookie(),
            'refresh' => $this->cookieService->forgetRefreshCookie(),
        ];

        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
            'cookies' => $cookies,
        ];
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(string $refreshToken): array
    {
        $refreshTokenRecord = RefreshToken::where('token', $refreshToken)
            ->whereHasMorph('authenticatable', [User::class])
            ->first();

        if (!$refreshTokenRecord) {
            return [
                'success' => false,
                'message' => 'Token de refresco inválido o expirado',
            ];
        }

        $user = $refreshTokenRecord->authenticatable;
        $accessToken = Auth::guard('api')->login($user);

        $newRefreshToken = JWTAuth::fromUser($user, ['exp' => now()->addDays(30)->timestamp]);
        $refreshTokenRecord->update([
            'token' => $newRefreshToken,
            'expires_at' => now()->addDays(30),
            'last_used_at' => now(),
        ]);

        $cookies = [
            'access' => $this->cookieService->makeAccessCookie($accessToken),
            'refresh' => $this->cookieService->makeRefreshCookie($newRefreshToken),
        ];

        return [
            'success' => true,
            'message' => 'Token renovado',
            'cookies' => $cookies,
        ];
    }

    /**
     * Generate access and refresh tokens for user
     */
    protected function generateTokensForUser(User $user): array
    {
        // Generate access token (JWT)
        $accessToken = JWTAuth::fromUser($user);

        // Create refresh token (JWT with longer expiration)
        $refreshToken = JWTAuth::fromUser($user, [
            'exp' => now()->addDays(30)->timestamp,
        ]);

        // Save or update refresh token in database
        $user->refreshTokens()->updateOrCreate(
            [],
            [
                'token' => $refreshToken,
                'expires_at' => now()->addDays(30),
                'last_used_at' => now(),
            ]
        );

        $cookies = [
            'access' => $this->cookieService->makeAccessCookie($accessToken),
            'refresh' => $this->cookieService->makeRefreshCookie($refreshToken),
        ];

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'cookies' => $cookies,
        ];
    }

    /**
     * Log user activity
     */
    protected function logUserActivity(User $user, string $message): void
    {
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['email' => $user->email])
            ->log($message);
    }
}
