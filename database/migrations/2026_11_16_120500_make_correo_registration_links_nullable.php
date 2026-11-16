<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correos', function (Blueprint $table) {
            $table->foreignId('pre_inscripcion_id')->nullable()->change();
            $table->foreignId('inscripcion_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $hasUnlinkedCorreo = DB::table('correos')
            ->whereNull('pre_inscripcion_id')
            ->orWhereNull('inscripcion_id')
            ->exists();

        if ($hasUnlinkedCorreo) {
            throw new RuntimeException(
                'Cannot require both correo links while records exist with only one related admission record.'
            );
        }

        Schema::table('correos', function (Blueprint $table) {
            $table->foreignId('pre_inscripcion_id')->nullable(false)->change();
            $table->foreignId('inscripcion_id')->nullable(false)->change();
        });
    }
};
