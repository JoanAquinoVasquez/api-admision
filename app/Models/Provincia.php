<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinciaFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    // Deshabilita los timestamps automáticos
    public $timestamps = false;

    // Relación muchos a uno con Departamento
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // Relación uno a muchos con Distrito
    public function distritos()
    {
        return $this->hasMany(Distrito::class);
    }
}
