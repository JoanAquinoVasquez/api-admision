<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesToAdmissionProcess;

class ComisionAdmision extends Model
{
    use HasFactory;
    use ScopesToAdmissionProcess;

    protected $fillable = [
        'facultad_id',
        'proceso_admision_id',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'email',
        'telefono',
        'resumen_completo',
        'estado',
    ];

    public function procesoAdmision(): BelongsTo
    {
        return $this->belongsTo(ProcesoAdmision::class);
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }
}
