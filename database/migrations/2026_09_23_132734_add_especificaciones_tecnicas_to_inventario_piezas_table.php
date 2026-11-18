<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->json('especificaciones_tecnicas')->nullable()->after('catalogo_pieza_id');
            // Como las RAMs, Discos y Cargadores pueden ser lotes sin conocer specs exactos
            // hasta que llegan
        });

        Schema::table('cargadores', function (Blueprint $table) {
            if (!Schema::hasColumn('cargadores', 'especificaciones_tecnicas')) {
                $table->json('especificaciones_tecnicas')->nullable()->after('punta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->dropColumn('especificaciones_tecnicas');
        });

        Schema::table('cargadores', function (Blueprint $table) {
            $table->dropColumn('especificaciones_tecnicas');
        });
    }
};
