<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            // Agregar lote de compra como texto libre (independiente de los lotes de equipos)
            $table->string('lote_compra', 100)->nullable()->after('folio')
                  ->comment('Referencia libre del lote de esta compra (ej: factura, importación)');

            // Agregar almacén destino global para toda la orden de compra
            $table->unsignedBigInteger('almacen_destino_id')->nullable()->after('lote_compra');
            $table->foreign('almacen_destino_id')->references('id')->on('almacenes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropForeign(['almacen_destino_id']);
            $table->dropColumn(['lote_compra', 'almacen_destino_id']);
        });
    }
};
