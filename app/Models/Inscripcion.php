<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory; // Descomentar y agregar LogsActivity

    protected $fillable = [
        'postulante_id',
        'programa_id',
        'voucher_id',
        'codigo',
        'val_digital',
        'val_fisico',
        'observacion',
        'estado',
    ];



    // Relación muchos a uno con Programa
    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    // Relación uno a uno con Voucher
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // Relación uno a uno con Postulante
    public function postulante()
    {
        return $this->belongsTo(Postulante::class);
    }

    // Relación uno a muchos con Correo
    public function correos()
    {
        return $this->hasMany(Correo::class);
    }

    // Relación one a one con Nota
    public function nota()
    {
        return $this->hasOne(Nota::class);
    }
}
