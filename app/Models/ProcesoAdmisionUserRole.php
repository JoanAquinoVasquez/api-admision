<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoAdmisionUserRole extends Model
{
    protected $table = 'proceso_admision_user_roles';

    protected $fillable = [
        'proceso_admision_id',
        'user_id',
        'role_id',
    ];

    public function procesoAdmision(): BelongsTo
    {
        return $this->belongsTo(ProcesoAdmision::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
