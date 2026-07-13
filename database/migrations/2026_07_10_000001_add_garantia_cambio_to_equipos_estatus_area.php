<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega GARANTIA_CAMBIO al ENUM equipos.estatus_area.
 *
 * Este estado identifica al equipo ORIGINAL que fue reemplazado físicamente
 * por el proveedor durante una garantía externa. El proveedor se queda con
 * ese equipo, por lo que ya no es recuperable ni reparable. Es diferente a
 * SCRAP (baja propia) y a GARANTIA_EXT (en espera de resolución).
 */
return new class extends Migration
{
    private string $enumConCambio = "ENUM(
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
        'GARANTIA_CAMBIO'
    ) NOT NULL DEFAULT 'EN_ESPERA'";

    private string $enumSinCambio = "ENUM(
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
        'GARANTIA_EXT'
    ) NOT NULL DEFAULT 'EN_ESPERA'";

    public function up(): void
    {
        // Liberar ENUM temporalmente para evitar problemas al agregar valor
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area VARCHAR(60) NOT NULL DEFAULT 'EN_ESPERA'");

        // Aplicar el nuevo ENUM con GARANTIA_CAMBIO
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area {$this->enumConCambio}");
    }

    public function down(): void
    {
        // Pasar equipos en GARANTIA_CAMBIO a GARANTIA_EXT antes de revertir
        DB::table('equipos')
            ->where('estatus_area', 'GARANTIA_CAMBIO')
            ->update(['estatus_area' => 'GARANTIA_EXT']);

        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area VARCHAR(60) NOT NULL DEFAULT 'EN_ESPERA'");
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area {$this->enumSinCambio}");
    }
};
