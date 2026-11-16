<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProcesoAdmision extends Model
{
    public const ESTADO_PREPARACION = 'preparacion';
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_CERRADO = 'cerrado';

    protected $table = 'procesos_admision';

    protected $fillable = [
        'codigo',
        'nombre',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'configuracion' => 'array',
        ];
    }

    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(Programa::class, 'programas_procesos')
            ->withPivot([
                'id',
                'estado',
                'vacantes',
                'concepto_pago_id',
                'docente_id',
                'docente_entrevista_id',
                'duracion_meses',
                'modalidad',
                'plan_estudio',
                'brochure',
            ])
            ->withTimestamps();
    }

    public function ofertasPrograma(): HasMany
    {
        return $this->hasMany(ProgramaProceso::class);
    }

    public function usuariosRoles(): HasMany
    {
        return $this->hasMany(ProcesoAdmisionUserRole::class);
    }

    public function preInscripciones(): HasMany
    {
        return $this->hasMany(PreInscripcion::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    public function esSoloLectura(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }
}
