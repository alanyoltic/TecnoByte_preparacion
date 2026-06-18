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
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            if (!Schema::hasColumn('asignacion_equipos', 'pre_asignado')) {
                $table->boolean('pre_asignado')->default(false)->after('camino')
                      ->comment('Marca si el AE fue creado por el gerente con serie pre-asignada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            if (Schema::hasColumn('asignacion_equipos', 'pre_asignado')) {
                $table->dropColumn('pre_asignado');
            }
        });
    }
};
