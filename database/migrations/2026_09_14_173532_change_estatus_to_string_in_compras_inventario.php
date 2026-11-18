<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En lugar de pelear con Doctrine y los ENUMs, convertimos a VARCHAR que es más seguro y escalable
        DB::statement("ALTER TABLE compras_inventario MODIFY estatus VARCHAR(50) NOT NULL DEFAULT 'PENDIENTE_GERENTE'");
    }

    public function down(): void
    {
        // Revertir a enum si fuera necesario (aunque normalmente se deja en varchar)
        DB::statement("ALTER TABLE compras_inventario MODIFY estatus ENUM('BORRADOR','PENDIENTE','RECIBIDA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
