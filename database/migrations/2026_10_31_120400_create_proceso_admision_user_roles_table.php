<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_admision_user_roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('proceso_admision_id')
                ->constrained('procesos_admision')
                ->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->timestamps();

            $table->unique(
                ['proceso_admision_id', 'user_id', 'role_id'],
                'proceso_usuario_rol_unique'
            );
            $table->index(['user_id', 'proceso_admision_id'], 'proceso_usuario_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_admision_user_roles');
    }
};
