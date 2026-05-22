<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preparacion_metas_mensuales', function (Blueprint $table) {
            if (!Schema::hasColumn('preparacion_metas_mensuales', 'lideres_iniciales')) {
                $table->unsignedInteger('lideres_iniciales')->default(0)->after('tecnicos_iniciales')
                    ->comment('Cantidad de líderes que trabajaban como técnicos al inicio del mes');
            }
            if (!Schema::hasColumn('preparacion_metas_mensuales', 'colaboradores_iniciales')) {
                $table->unsignedInteger('colaboradores_iniciales')->default(0)->after('lideres_iniciales')
                    ->comment('Total: técnicos + líderes al inicio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('preparacion_metas_mensuales', function (Blueprint $table) {
            if (Schema::hasColumn('preparacion_metas_mensuales', 'lideres_iniciales')) {
                $table->dropColumn(['lideres_iniciales', 'colaboradores_iniciales']);
            }
        });
    }
};
