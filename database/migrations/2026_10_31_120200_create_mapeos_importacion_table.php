<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapeos_importacion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('fuente', 20);
            $table->string('tabla_origen', 64);
            $table->string('id_origen', 191);
            $table->string('tabla_destino', 64);
            $table->unsignedBigInteger('id_destino');
            $table->timestamps();

            $table->unique(
                ['fuente', 'tabla_origen', 'id_origen'],
                'mapeos_importacion_origen_unique'
            );
            $table->index(
                ['tabla_destino', 'id_destino'],
                'mapeos_importacion_destino_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapeos_importacion');
    }
};
