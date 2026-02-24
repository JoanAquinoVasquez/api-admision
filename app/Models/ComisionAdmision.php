<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionAdmision extends Model
{
    use HasFactory;

    protected $fillable = [
        'facultad_id',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'email',
        'telefono',
        'resumen_completo',
        'estado',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }
}
