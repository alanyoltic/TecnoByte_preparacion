<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige los valores de estatus_area para que reflejen el flujo real:
 *
 *  EN_ESPERA -> ASIGNADO -> EN_PROCESO -> EN_CALIDAD -> FINALIZADO
 *                                      -> PENDIENTE_PIEZA
 *                                      -> PENDIENTE_GARANTIA
 *                                      -> PENDIENTE_DESARME
 *
 * Cambios de datos:
 *  LISTO              -> EN_CALIDAD    (tecnico termino bien, espera calidad)
 *  TRANSFERIDO        -> FINALIZADO    (calidad aprobó, excluye ventas+)
 *  PENDIENTE_DESHUESO -> PENDIENTE_DESARME
 *  SIN_ASIGNAR (CEDIS)-> EN_ESPERA    (lote registrado, nadie lo ha tocado)
 *
 * IMPORTANTE: primero se cambia a VARCHAR para migrar datos sin restriccion
 * de ENUM, luego se aplica el nuevo ENUM.
 */
return new class extends Migration
{
    private string $enumNuevo = "ENUM(
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

    private string $enumViejo = "ENUM(
        'SIN_ASIGNAR',
        'ASIGNADO',
        'EN_PROCESO',
        'LISTO',
        'TRANSFERIDO',
        'PENDIENTE_PIEZA',
        'PENDIENTE_GARANTIA',
        'PENDIENTE_DESHUESO',
        'GARANTIA_INT',
        'GARANTIA_EXT'
    ) NOT NULL DEFAULT 'SIN_ASIGNAR'";

    public function up(): void
    {
        // Paso 1: liberar restriccion ENUM para poder migrar datos
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area VARCHAR(60) NOT NULL DEFAULT 'EN_ESPERA'");

        // Paso 2: migrar datos al nuevo vocabulario
        DB::table('equipos')->where('estatus_area', 'LISTO')
            ->update(['estatus_area' => 'EN_CALIDAD']);

        DB::table('equipos')->where('estatus_area', 'TRANSFERIDO')
            ->whereNotIn('estatus_ciclo', ['VENTAS', 'APARTADO', 'VENDIDO', 'SCRAP'])
            ->update(['estatus_area' => 'FINALIZADO']);

        DB::table('equipos')->where('estatus_area', 'PENDIENTE_DESHUESO')
            ->update(['estatus_area' => 'PENDIENTE_DESARME']);

        DB::table('equipos')->where('estatus_area', 'SIN_ASIGNAR')
            ->where('estatus_ciclo', 'CEDIS')
            ->update(['estatus_area' => 'EN_ESPERA']);

        // Paso 3: aplicar el nuevo ENUM
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area {$this->enumNuevo}");
    }

    public function down(): void
    {
        // Liberar ENUM para revertir datos
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area VARCHAR(60) NOT NULL DEFAULT 'SIN_ASIGNAR'");

        DB::table('equipos')->where('estatus_area', 'EN_CALIDAD')
            ->update(['estatus_area' => 'LISTO']);

        DB::table('equipos')->where('estatus_area', 'FINALIZADO')
            ->update(['estatus_area' => 'TRANSFERIDO']);

        DB::table('equipos')->where('estatus_area', 'PENDIENTE_DESARME')
            ->update(['estatus_area' => 'PENDIENTE_DESHUESO']);

        DB::table('equipos')->where('estatus_area', 'EN_ESPERA')
            ->update(['estatus_area' => 'SIN_ASIGNAR']);

        // Restaurar ENUM original
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estatus_area {$this->enumViejo}");
    }
};
