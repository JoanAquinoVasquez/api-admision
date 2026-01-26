<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    // Deshabilita los timestamps automáticos
    public $timestamps = false;

    
    // Relación uno a muchos con Provincia
    public function provincias()
    {
        return $this->hasMany(Provincia::class);
    }
}
