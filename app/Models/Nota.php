<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscripcion_id',
        'cv',
        'entrevista',
        'examen',
        'final',
    ];

    // Relación uno a uno inversa
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    // Relación uno a muchos con Docente
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
}
