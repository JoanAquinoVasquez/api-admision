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
        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained();
            $table->foreignId('facultad_id')->constrained();
            $table->foreignId('concepto_pago_id')->constrained();
            $table->foreignId('docente_id')->nullable()->constrained();
            $table->string('nombre');
            $table->string('vacantes');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programas');
    }
};
