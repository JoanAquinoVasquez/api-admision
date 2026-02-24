<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoPago extends Model
{
    /** @use HasFactory<\Database\Factories\ConceptoPagoFactory> */
    use HasFactory;

    protected $fillable = [
        'cod_concepto',
        'nombre',
        'monto',
        'estado',
    ];

    // Relación uno a muchos con Voucher
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    // Relación uno a muchos con Programa
    public function programas()
    {
        return $this->hasMany(Programa::class);
    }
}
