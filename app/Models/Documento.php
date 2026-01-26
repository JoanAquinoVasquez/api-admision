<?php

namespace App\Models;

use Google\Service\Blogger\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'postulante_id',
        'tipo',
        'nombre_archivo',
        'url',
        'estado',
    ];

    // Relación muchos a uno con Postulante
    public function postulante()
    {
        return $this->belongsTo(Postulante::class);
    }
}
