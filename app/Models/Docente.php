<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Docente extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombres',
        'ap_paterno',
        'ap_materno',
        'dni',
        'email',
        'estado',
    ];

    protected $hidden = [
        'password',
        /* 'remember_token', */
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relación uno a muchos con la tabla de Programas
     */
    public function programas()
    {
        return $this->hasMany(Programa::class);
    }

    // Métodos requeridos por JWTAuth
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function refreshTokens()
    {
        return $this->morphMany(RefreshToken::class, 'authenticatable');
    }
}
