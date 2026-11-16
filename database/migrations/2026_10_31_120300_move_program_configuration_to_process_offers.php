<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->unsignedBigInteger('concepto_pago_id')->nullable()->change();
            $table->string('vacantes')->nullable()->change();
            $table->unique(
                ['grado_id', 'facultad_id', 'nombre'],
                'programas_identidad_catalogo_unique'
            );
        });

        Schema::table('programas_procesos', function (Blueprint $table) {
            $table->string('vacantes')->nullable()->change();
            $table->unsignedInteger('duracion_meses')->nullable()->after('vacantes');
            $table->string('modalidad')->nullable()->after('duracion_meses');
            $table->text('plan_estudio')->nullable()->after('modalidad');
            $table->text('brochure')->nullable()->after('plan_estudio');
            $table->foreignId('docente_id')->nullable()->after('concepto_pago_id')
                ->constrained('docentes')->nullOnDelete();
            $table->foreignId('docente_entrevista_id')->nullable()->after('docente_id')
                ->constrained('docentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $hasNullCatalogFields = DB::table('programas')
            ->whereNull('concepto_pago_id')
            ->orWhereNull('vacantes')
            ->exists();

        if ($hasNullCatalogFields) {
            throw new RuntimeException(
                'Cannot restore required global program fields while any program has period-specific values only.'
            );
        }

        $hasNonNumericOfferVacancies = DB::table('programas_procesos')
            ->whereNotNull('vacantes')
            ->whereRaw("vacantes NOT REGEXP '^[0-9]+$'")
            ->exists();

        if ($hasNonNumericOfferVacancies) {
            throw new RuntimeException(
                'Cannot restore numeric offer vacancies while a value is not an integer.'
            );
        }

        Schema::table('programas_procesos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('docente_entrevista_id');
            $table->dropConstrainedForeignId('docente_id');
            $table->dropColumn(['duracion_meses', 'modalidad', 'plan_estudio', 'brochure']);
            $table->unsignedInteger('vacantes')->nullable()->change();
        });

        Schema::table('programas', function (Blueprint $table) {
            $table->dropUnique('programas_identidad_catalogo_unique');
            $table->unsignedBigInteger('concepto_pago_id')->nullable(false)->change();
            $table->string('vacantes')->nullable(false)->change();
        });
    }
};
