
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->unique('inscripcion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->dropUnique(['inscripcion_id']);
        });
    }
};
