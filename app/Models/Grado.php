<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    /** @use HasFactory<\Database\Factories\GradoFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    // Deshabilita los timestamps automáticos
    public $timestamps = false;

    // Relación uno a muchos con Programa
    public function programas()
    {
        return $this->hasMany(Programa::class);
    }    
}
