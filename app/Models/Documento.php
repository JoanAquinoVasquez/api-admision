<?php

namespace App\Models;

use Google\Service\Blogger\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesToAdmissionProcess;

class Documento extends Model
{
    use HasFactory;
    use ScopesToAdmissionProcess;

    protected $fillable = [
        'postulante_id',
        'proceso_admision_id',
        'tipo',
        'nombre_archivo',
        'url',
        'estado',
    ];

    public function procesoAdmision(): BelongsTo
    {
        return $this->belongsTo(ProcesoAdmision::class);
    }

    // Relación muchos a uno con Postulante
    public function postulante()
    {
        return $this->belongsTo(Postulante::class);
    }
}
