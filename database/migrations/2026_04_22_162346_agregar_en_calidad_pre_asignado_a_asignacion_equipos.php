<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
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
            'DESPIECE'
        ) NOT NULL DEFAULT 'EN_PROCESO'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE asignacion_equipos MODIFY COLUMN camino ENUM(
            'PENDIENTE',
            'EN_PROCESO',
            'COMPLETADO',
            'PIEZA_PENDIENTE',
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA',
            'DESPIECE'
        ) NOT NULL DEFAULT 'EN_PROCESO'");
    }
};
