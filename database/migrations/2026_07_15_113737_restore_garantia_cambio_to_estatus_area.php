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
        DB::statement("
            ALTER TABLE equipos
            MODIFY COLUMN estatus_area ENUM(
                'EN_ESPERA',
                'SIN_ASIGNAR',
                'ASIGNADO',
                'EN_PROCESO',
                'EN_CALIDAD',
                'FINALIZADO',
                'TRANSFERIDO',
                'PENDIENTE_PIEZA',
                'PENDIENTE_GARANTIA',
                'PENDIENTE_DESARME',
                'GARANTIA_INT',
                'GARANTIA_EXT',
                'GARANTIA_CAMBIO',
                'DISPONIBLE_VENTA',
                'EN_PISO_VENTA',
                'APARTADO_CLIENTE',
                'EN_GARANTIA_CLIENTE'
            ) NOT NULL DEFAULT 'EN_ESPERA'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE equipos
            MODIFY COLUMN estatus_area ENUM(
                'EN_ESPERA',
                'SIN_ASIGNAR',
                'ASIGNADO',
                'EN_PROCESO',
                'EN_CALIDAD',
                'FINALIZADO',
                'TRANSFERIDO',
                'PENDIENTE_PIEZA',
                'PENDIENTE_GARANTIA',
                'PENDIENTE_DESARME',
                'GARANTIA_INT',
                'GARANTIA_EXT',
                'DISPONIBLE_VENTA',
                'EN_PISO_VENTA',
                'APARTADO_CLIENTE',
                'EN_GARANTIA_CLIENTE'
            ) NOT NULL DEFAULT 'EN_ESPERA'
        ");
    }
};
