<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el enum de camino con PENDIENTE y DESPIECE
        DB::statement("ALTER TABLE asignacion_equipos MODIFY COLUMN camino ENUM(
            'PENDIENTE',
            'EN_PROCESO',
            'COMPLETADO',
            'PIEZA_PENDIENTE',
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA',
            'DESPIECE'
        ) NOT NULL DEFAULT 'EN_PROCESO'");

        Schema::table('asignacion_equipos', function (Blueprint $table) {
            // Marca si el AE fue creado por el gerente con serie pre-asignada
            // (true = reset al borrar, false = borrado total)
            $table->boolean('pre_asignado')->default(false)->after('camino');
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            $table->dropColumn('pre_asignado');
        });

        DB::statement("ALTER TABLE asignacion_equipos MODIFY COLUMN camino ENUM(
            'EN_PROCESO',
            'COMPLETADO',
            'PIEZA_PENDIENTE',
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA'
        ) NOT NULL DEFAULT 'EN_PROCESO'");
    }
};
