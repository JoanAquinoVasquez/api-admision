<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    /** @use HasFactory<\Database\Factories\FacultadFactory> */
    use HasFactory;

    protected $table = 'facultads';
    protected $fillable = ['nombre', 'siglas', 'estado'];

    // Deshabilita los timestamps automáticos
    public $timestamps = false;
    
    // Relación uno a muchos con Programa
    public function programas()
    {
        return $this->hasMany(Programa::class);
    }
}
