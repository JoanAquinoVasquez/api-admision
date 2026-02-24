<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('concepto_pagos', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('cod_concepto'); // Permitimos duplicados
            $table->string('nombre');
            $table->decimal('monto', 6, 2); // Aumentamos la precisión del monto, si es necesario
            $table->boolean('estado')->default(true);
            $table->timestamps();

            // Si necesitas que cod_concepto sea único, comenta la siguiente línea
            // $table->unique('cod_concepto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concepto_pagos');
    }
};
