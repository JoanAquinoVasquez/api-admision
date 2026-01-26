<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInscripcion extends Model
{
    use HasFactory;

    protected $fillable = [
        'postulante_id',
        'distrito_id',
        'programa_id',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'email',
        'tipo_doc',
        'num_iden',
        'fecha_nacimiento',
        'sexo',
        'celular',
        'uni_procedencia',
        'centro_trabajo',
        'cargo',
        'estado',
    ];

    // Relación muchos a uno con Distrito
    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    // Relaciín muchosa uno con Postulante
    public function postulante()
    {
        return $this->belongsTo(Postulante::class);
    }

    // Relación muchos a uno con Programa
    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    // Relación uno a muchos con Correo
    public function correos()
    {
        return $this->hasMany(Correo::class);
    }
}
