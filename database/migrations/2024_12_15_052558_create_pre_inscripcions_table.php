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
        Schema::create('pre_inscripcions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->nullable()->constrained(); // Permite NULL y mantiene la clave foránea
            $table->foreignId('distrito_id')->constrained();
            $table->foreignId('programa_id')->constrained();
            $table->string('nombres');
            $table->string('ap_paterno');
            $table->string('ap_materno');
            $table->string('email');
            $table->string('tipo_doc');
            $table->string('num_iden', 20)->unique();
            $table->date('fecha_nacimiento');
            $table->char('sexo', 1);
            $table->string('celular');
            $table->string('uni_procedencia')->nullable();
            $table->string('centro_trabajo')->nullable();
            $table->string('cargo')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_inscripcions');
    }
};
