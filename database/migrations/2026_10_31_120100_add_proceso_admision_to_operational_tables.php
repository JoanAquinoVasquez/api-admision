<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PROCESS_INDEXES = [
        'pre_inscripcions' => [
            'pre_inscripcions_proceso_programa_idx' => ['proceso_admision_id', 'programa_id'],
            'pre_inscripcions_proceso_num_iden_unique' => ['proceso_admision_id', 'num_iden'],
        ],
        'inscripcions' => ['inscripcions_proceso_estado_idx' => ['proceso_admision_id', 'estado']],
        'vouchers' => [
            'vouchers_proceso_num_iden_idx' => ['proceso_admision_id', 'num_iden'],
            'vouchers_proceso_fecha_pago_idx' => ['proceso_admision_id', 'fecha_pago'],
        ],
        'documentos' => ['documentos_proceso_postulante_idx' => ['proceso_admision_id', 'postulante_id']],
        'comision_admisions' => ['comision_admisions_proceso_estado_idx' => ['proceso_admision_id', 'estado']],
    ];

    public function up(): void
    {
        Schema::create('programas_procesos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('proceso_admision_id')->constrained('procesos_admision')->restrictOnDelete();
            $table->foreignId('programa_id')->constrained('programas')->restrictOnDelete();
            $table->string('estado', 30)->default('habilitado');
            $table->unsignedInteger('vacantes')->nullable();
            $table->foreignId('concepto_pago_id')->nullable()->constrained('concepto_pagos')->nullOnDelete();
            $table->timestamps();
            $table->unique(['proceso_admision_id', 'programa_id'], 'programas_procesos_proceso_programa_unique');
            $table->index(['proceso_admision_id', 'estado'], 'programas_procesos_proceso_estado_idx');
        });

        foreach (array_keys(self::PROCESS_INDEXES) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('proceso_admision_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('procesos_admision')
                    ->restrictOnDelete();
            });
        }

        Schema::table('pre_inscripcions', function (Blueprint $table) {
            // DNI sigue siendo único para la persona; una preinscripción se limita por proceso.
            $table->dropUnique(['num_iden']);
            $table->index(
                ['proceso_admision_id', 'programa_id'],
                'pre_inscripcions_proceso_programa_idx'
            );
            $table->unique(
                ['proceso_admision_id', 'num_iden'],
                'pre_inscripcions_proceso_num_iden_unique'
            );
        });

        Schema::table('inscripcions', function (Blueprint $table) {
            $table->index(['proceso_admision_id', 'estado'], 'inscripcions_proceso_estado_idx');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->index(['proceso_admision_id', 'num_iden'], 'vouchers_proceso_num_iden_idx');
            $table->index(['proceso_admision_id', 'fecha_pago'], 'vouchers_proceso_fecha_pago_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->index(
                ['proceso_admision_id', 'postulante_id'],
                'documentos_proceso_postulante_idx'
            );
        });

        Schema::table('comision_admisions', function (Blueprint $table) {
            $table->index(
                ['proceso_admision_id', 'estado'],
                'comision_admisions_proceso_estado_idx'
            );
        });
    }

    public function down(): void
    {
        $duplicateDni = DB::table('pre_inscripcions')
            ->select('num_iden')
            ->groupBy('num_iden')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateDni) {
            throw new RuntimeException(
                'Cannot roll back process-scoped pre-registration uniqueness while a DNI has records in multiple admission processes.'
            );
        }

        Schema::table('pre_inscripcions', function (Blueprint $table) {
            $table->dropUnique('pre_inscripcions_proceso_num_iden_unique');
            $table->dropIndex('pre_inscripcions_proceso_programa_idx');
            $table->unique('num_iden');
        });

        Schema::table('inscripcions', function (Blueprint $table) {
            $table->dropIndex('inscripcions_proceso_estado_idx');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex('vouchers_proceso_num_iden_idx');
            $table->dropIndex('vouchers_proceso_fecha_pago_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('documentos_proceso_postulante_idx');
        });

        Schema::table('comision_admisions', function (Blueprint $table) {
            $table->dropIndex('comision_admisions_proceso_estado_idx');
        });

        foreach (array_keys(self::PROCESS_INDEXES) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('proceso_admision_id');
            });
        }

        Schema::dropIfExists('programas_procesos');
    }
};
