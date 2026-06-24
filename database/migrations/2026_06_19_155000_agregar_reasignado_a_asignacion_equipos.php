<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE asignacion_equipos MODIFY COLUMN camino ENUM(
            'PENDIENTE',
            'PRE_ASIGNADO',
            'EN_PROCESO',
            'EN_CALIDAD',
            'COMPLETADO',
            'PIEZA_PENDIENTE',
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA',
            'DESPIECE',
            'REASIGNADO'
        ) NOT NULL DEFAULT 'EN_PROCESO'");
    }

    public function down(): void
    {
        // Revertir a la versión anterior (sin REASIGNADO)
        // Nota: Si hay registros con REASIGNADO, el down fallará a menos que se cambien a otro valor antes.
        DB::statement("ALTER TABLE asignacion_equipos MODIFY COLUMN camino ENUM(
            'PENDIENTE',
            'PRE_ASIGNADO',
            'EN_PROCESO',
            'EN_CALIDAD',
            'COMPLETADO',
            'PIEZA_PENDIENTE',
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA',
            'DESPIECE'
        ) NOT NULL DEFAULT 'EN_PROCESO'");
    }
};
