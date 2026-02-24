<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    /** @use HasFactory<\Database\Factories\DistritoFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    // Deshabilita los timestamps automáticos
    public $timestamps = false;

    // Relación muchos a uno con Provincia
    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    // Relación uno a muchos con PreInscripcion
    public function preInscripciones()
    {
        return $this->hasMany(PreInscripcion::class);
    }

    // Relación uno a muchois con Postulante
    public function postulantes()
    {
        return $this->hasMany(Postulante::class);
    }
}
