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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concepto_pago_id')->constrained('concepto_pagos');
            $table->string('numero', 7);
            $table->string('num_iden', 20);
            $table->string('nombre_completo', 150);
            $table->decimal('monto', 6, 2);
            $table->date('fecha_pago');
            $table->time('hora_pago');
            $table->char('cajero', 4);
            $table->char('agencia', 4);
            $table->boolean('estado')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
