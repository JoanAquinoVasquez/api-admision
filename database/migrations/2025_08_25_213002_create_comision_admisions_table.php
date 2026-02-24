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
        Schema::create('comision_admisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facultad_id')->nullable()->constrained();
            $table->string('nombres');
            $table->string('ap_paterno');
            $table->string('ap_materno');
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->boolean('resumen_completo')->default(false);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_admisions');
    }
};
