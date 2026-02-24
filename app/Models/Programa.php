<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramaFactory> */
    use HasFactory;

    // Ocultar los campos created_at y updated_at
    protected $hidden = ['created_at', 'updated_at'];

    // Cast fields to ensure they're always included in JSON
    protected $casts = [
        'plan_estudio' => 'string',
        'brochure' => 'string',
    ];

    protected $fillable = [
        'facultad_id',
        'grado_id',
        'concepto_pago_id',
        'docente_id',
        'nombre',
        'vacantes',
        'estado',
        'plan_estudio',
        'brochure',
    ];

    // Relación muchos a uno con Facultad
    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    // Relación muchos a uno con Grado
    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    // Relación muchos a uno con ConceptoPago
    public function conceptoPago()
    {
        return $this->belongsTo(ConceptoPago::class);
    }

    // Relación muchos a uno con Docente
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    // Relación uno a muchos con Inscripcion
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Relación uno a muchos con PreInscripcion
    public function preInscripciones()
    {
        return $this->hasMany(PreInscripcion::class);
    }
}
