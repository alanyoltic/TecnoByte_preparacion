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
    Schema::table('equipos', function (Blueprint $table) {
        $table->boolean('tiene_grafica_integrada')->default(false)->after('tipo_equipo');
        $table->boolean('tiene_grafica_dedicada')->default(false)->after('tiene_grafica_integrada');
        $table->index(['tiene_grafica_integrada','tiene_grafica_dedicada'], 'idx_equipos_gpu_flags');
    });
}

public function down(): void
{
    Schema::table('equipos', function (Blueprint $table) {
        $table->dropIndex('idx_equipos_gpu_flags');
        $table->dropColumn(['tiene_grafica_integrada','tiene_grafica_dedicada']);
    });
}

};
