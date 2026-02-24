<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    /** @use HasFactory<\Database\Factories\PostulanteFactory> */
    use HasFactory;

    protected $fillable = [
        'distrito_id',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'email',
        'tipo_doc',
        'num_iden',
        'fecha_nacimiento',
        'sexo',
        'celular',
        'direccion',
        'estado',
    ];



    // Relación muchos a uno con Distrito
    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    // Relacion uno a uno con PreInscripcion
    public function preInscripcion()
    {
        return $this->hasOne(PreInscripcion::class);
    }

    // Relación uno a muchos con Documento
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    // Relacion uno a uno con Inscripcion
    public function inscripcion()
    {
        return $this->hasOne(Inscripcion::class);
    }
}
