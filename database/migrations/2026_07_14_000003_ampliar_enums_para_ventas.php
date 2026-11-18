<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Amplía los ENUMs de:
 * 1. equipos.estatus_area    → agrega estados propios de Ventas
 * 2. equipo_movimientos.tipo → agrega tipos de movimientos de Ventas
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. equipos.estatus_area ─────────────────────────────────────────
        // MariaDB/MySQL no permite ALTER COLUMN ENUM directamente sin reconstruir.
        // Se cambia a VARCHAR temporalmente, se actualiza el ENUM y listo.
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

        // ── 2. equipo_movimientos.tipo ──────────────────────────────────────
        DB::statement("
            ALTER TABLE equipo_movimientos
            MODIFY COLUMN tipo ENUM(
                'ALTA_LOTE',
                'ALTA_MANUAL',
                'MOVER_ALMACEN',
                'ASIGNAR_TECNICO',
                'FINALIZAR_TECNICO',
                'VENTA',
                'BAJA',
                'AJUSTE',
                'DESPACHO_VENTAS',
                'APARTADO_CLIENTE',
                'VENTA_CONCRETADA',
                'GARANTIA_CLIENTE_ENTRADA',
                'GARANTIA_CLIENTE_SALIDA'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Revertir estatus_area
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
                'GARANTIA_EXT'
            ) NOT NULL DEFAULT 'EN_ESPERA'
        ");

        // Revertir tipos de movimiento
        DB::statement("
            ALTER TABLE equipo_movimientos
            MODIFY COLUMN tipo ENUM(
                'ALTA_LOTE',
                'ALTA_MANUAL',
                'MOVER_ALMACEN',
                'ASIGNAR_TECNICO',
                'FINALIZAR_TECNICO',
                'VENTA',
                'BAJA',
                'AJUSTE'
            ) NOT NULL
        ");
    }
};
