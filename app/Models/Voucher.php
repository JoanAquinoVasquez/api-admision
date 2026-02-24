<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\VoucherFactory> */
    use HasFactory;

    protected $fillable = [
        'concepto_pago_id',
        'numero',
        'num_iden',
        'nombre_completo',
        'monto',
        'fecha_pago',
        'hora_pago',
        'cajero',
        'agencia',
        'estado'        
    ];

    // Relacion muchos a uno con Concepto de Pago
    public function conceptoPago()
    {
        return $this->belongsTo(ConceptoPago::class);
    }
    
    // Relación uno a uno con Inscripcion
    public function inscripcion()
    {
        return $this->hasOne(Inscripcion::class);
    }
}
