<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->enum('estatus', ['BORRADOR', 'PENDIENTE', 'RECIBIDA', 'CANCELADA'])
                  ->default('PENDIENTE')
                  ->after('folio')
                  ->comment('Estado del flujo de la orden de compra');
        });

        // Las compras hechas antes de hoy ya sumaron stock, así que están RECIBIDAS
        DB::table('compras_inventario')->update(['estatus' => 'RECIBIDA']);
    }

    public function down(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
