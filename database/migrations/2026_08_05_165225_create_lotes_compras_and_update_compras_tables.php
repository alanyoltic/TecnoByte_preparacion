<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear la tabla de lotes de compra
        Schema::create('lotes_compras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('area', 50)->comment('PREPARACION o VENTAS');
            $table->text('descripcion')->nullable();
            $table->date('fecha_llegada')->nullable();
            $table->enum('estatus', ['PENDIENTE', 'RECIBIDO', 'CERRADO'])->default('PENDIENTE');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Modificar la tabla compras_inventario
        Schema::table('compras_inventario', function (Blueprint $table) {
            // Eliminar la columna 'lote_compra' que era un string libre (creado hace poco)
            $table->dropColumn('lote_compra');
            
            // Agregar la verdadera llave foránea al lote de compra
            $table->unsignedBigInteger('lote_compra_id')->nullable()->after('folio');
            $table->foreign('lote_compra_id')->references('id')->on('lotes_compras')->nullOnDelete();
        });

        // 3. Modificar la tabla compras_inventario_items para soportar Productos y Consumibles
        Schema::table('compras_inventario_items', function (Blueprint $table) {
            $table->unsignedBigInteger('catalogo_pieza_id')->nullable()->change();
            
            $table->unsignedBigInteger('producto_id')->nullable()->after('catalogo_pieza_id');
            $table->foreign('producto_id')->references('id')->on('productos')->nullOnDelete();

            $table->unsignedBigInteger('consumible_id')->nullable()->after('producto_id');
            $table->foreign('consumible_id')->references('id')->on('consumibles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compras_inventario_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['consumible_id']);
            $table->dropColumn(['producto_id', 'consumible_id']);
        });

        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropForeign(['lote_compra_id']);
            $table->dropColumn('lote_compra_id');
            $table->string('lote_compra', 100)->nullable()->after('folio');
        });

        Schema::dropIfExists('lotes_compras');
    }
};
