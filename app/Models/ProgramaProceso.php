<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramaProceso extends Model
{
    protected $table = 'programas_procesos';

    protected $fillable = [
        'proceso_admision_id',
        'programa_id',
        'estado',
        'vacantes',
        'concepto_pago_id',
        'docente_id',
        'docente_entrevista_id',
        'duracion_meses',
        'modalidad',
        'plan_estudio',
        'brochure',
    ];

    public function procesoAdmision(): BelongsTo
    {
        return $this->belongsTo(ProcesoAdmision::class);
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class);
    }

    public function conceptoPago(): BelongsTo
    {
        return $this->belongsTo(ConceptoPago::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function docenteEntrevista(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_entrevista_id');
    }
}
