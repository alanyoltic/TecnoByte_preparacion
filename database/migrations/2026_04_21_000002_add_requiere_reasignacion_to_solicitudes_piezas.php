<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE solicitudes_piezas MODIFY COLUMN estatus ENUM('PENDIENTE','SURTIDA_INVENTARIO','PENDIENTE_COMPRA','COMPRADA','CANCELADA','CONFIRMADA','REQUIERE_REASIGNACION') NOT NULL DEFAULT 'PENDIENTE'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE solicitudes_piezas MODIFY COLUMN estatus ENUM('PENDIENTE','SURTIDA_INVENTARIO','PENDIENTE_COMPRA','COMPRADA','CANCELADA','CONFIRMADA') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
