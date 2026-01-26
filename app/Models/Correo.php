<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correo extends Model
{
    /** @use HasFactory<\Database\Factories\CorreoFactory> */
    use HasFactory;

    protected $fillable = [
        'inscripcion_id',
        'pre_inscripcion_id',
        'tipo',
        'detalle',
    ];

    // Relación muchos a uno con Inscripcion
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    // Relación muchos a uno con PreInscripcion
    public function preInscripcion()
    {
        return $this->belongsTo(PreInscripcion::class);
    }
}
