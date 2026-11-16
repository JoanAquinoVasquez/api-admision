<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapeoImportacion extends Model
{
    protected $table = 'mapeos_importacion';

    protected $fillable = [
        'fuente',
        'tabla_origen',
        'id_origen',
        'tabla_destino',
        'id_destino',
    ];
}
