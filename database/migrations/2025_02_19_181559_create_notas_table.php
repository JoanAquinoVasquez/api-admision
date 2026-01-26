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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained();
            $table->decimal('cv', 5, 3)->nullable();
            $table->decimal('entrevista', 5, 3)->nullable();
            $table->decimal('examen', 5, 3)->nullable();
            $table->decimal('final', 5, 3)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
