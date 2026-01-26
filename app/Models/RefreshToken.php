<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = ['authenticatable_type', 'authenticatable_id', 'token', 'expires_at', 'last_used_at'];
    protected $dates = ['expires_at'];

    public function authenticatable()
    {
        return $this->morphTo();
    }
}
