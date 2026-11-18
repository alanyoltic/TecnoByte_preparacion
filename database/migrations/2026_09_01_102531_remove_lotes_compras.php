<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Quitar la relación de lote en compras_inventario
        if (Schema::hasTable('compras_inventario') && Schema::hasColumn('compras_inventario', 'lote_compra_id')) {
            Schema::table('compras_inventario', function (Blueprint $table) {
                $table->dropForeign(['lote_compra_id']);
                $table->dropColumn('lote_compra_id');
            });
        }

        // 2. Borrar la tabla de lotes de compras si existe
        if (Schema::hasTable('lotes_compras')) {
            Schema::dropIfExists('lotes_compras');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('lotes_compras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('estatus')->default('ABIERTO');
            $table->timestamps();
        });

        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->foreignId('lote_compra_id')->nullable()->constrained('lotes_compras')->nullOnDelete();
        });
    }
};
