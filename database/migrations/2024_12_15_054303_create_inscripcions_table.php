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
        Schema::create('inscripcions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained();
            $table->foreignId('programa_id')->constrained();
            $table->foreignId('voucher_id')->constrained();
            $table->string('codigo', 7);
            $table->tinyInteger('val_digital');
            $table->boolean('val_fisico');
            $table->string('observacion')->nullable();
            $table->boolean('estado')->default(1); // 1: Activo, 0: Inhabilitado, 2: Reservado, 3: Devolución
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcions');
    }
};
