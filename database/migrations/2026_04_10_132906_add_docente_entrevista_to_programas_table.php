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
        Schema::table('programas', function (Blueprint $table) {
            $table->foreignId('docente_entrevista_id')->nullable()->after('docente_id')->constrained('docentes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropForeign(['docente_entrevista_id']);
            $table->dropColumn('docente_entrevista_id');
        });
    }
};
