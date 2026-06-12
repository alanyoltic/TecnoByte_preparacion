<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega ALTA_MANUAL al ENUM de equipo_movimientos.tipo
        // (se usa al registrar/crear equipos manualmente desde Livewire).
        DB::statement("ALTER TABLE equipo_movimientos MODIFY COLUMN tipo ENUM(
            'ALTA_LOTE',
            'ALTA_MANUAL',
            'MOVER_ALMACEN',
            'ASIGNAR_TECNICO',
            'FINALIZAR_TECNICO',
            'VENTA',
            'BAJA',
            'AJUSTE'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Nota: si existen registros con tipo=ALTA_MANUAL este downgrade puede fallar.
        DB::statement("ALTER TABLE equipo_movimientos MODIFY COLUMN tipo ENUM(
            'ALTA_LOTE',
            'MOVER_ALMACEN',
            'ASIGNAR_TECNICO',
            'FINALIZAR_TECNICO',
            'VENTA',
            'BAJA',
            'AJUSTE'
        ) NOT NULL");
    }
};
