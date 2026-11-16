<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'profile_picture',
        'remember_token',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Relaciones con la tabla roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Verifica si el usuario tiene alguno de los roles especificados
    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('slug', (array) $roles)->exists();
    }

    public function procesoRoles(): HasMany
    {
        return $this->hasMany(ProcesoAdmisionUserRole::class);
    }

    public function hasRoleForProcess(int $processId, array|string $roles): bool
    {
        $roles = (array) $roles;

        if ($this->hasAnyRole(['super-admin'])) {
            return true;
        }

        return $this->procesoRoles()
            ->where('proceso_admision_id', $processId)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', $roles))
            ->exists();
    }

    public function hasAnyProcessRole(array|string $roles): bool
    {
        return $this->procesoRoles()
            ->whereHas('role', fn ($query) => $query->whereIn('slug', (array) $roles))
            ->exists();
    }

    /** @return list<string> */
    public function effectiveRoleSlugs(): array
    {
        $global = $this->roles()->pluck('slug')->all();
        $scoped = $this->procesoRoles()->with('role:id,slug')->get()
            ->pluck('role.slug')->filter()->all();

        return array_values(array_unique(array_merge($global, $scoped)));
    }

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $roles = $this->effectiveRoleSlugs();
        return [
            'role' => $roles[0] ?? null,
            'roles' => $roles,
        ];
    }

    public function refreshTokens()
    {
        return $this->morphMany(RefreshToken::class, 'authenticatable');
    }
}
