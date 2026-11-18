<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('folio');
            $table->decimal('iva', 10, 2)->default(0)->after('subtotal');
            $table->decimal('total', 10, 2)->default(0)->after('iva');
            $table->string('tipo_iva', 50)->default('INCLUIDO')->after('total'); // INCLUIDO, MAS_IVA, EXENTO
            $table->foreignId('aprobado_por_id')->nullable()->constrained('users')->nullOnDelete()->after('registrado_por_id');
            $table->string('nombre_lote_propuesto')->nullable()->after('notas');
        });

        Schema::table('compras_inventario_items', function (Blueprint $table) {
            $table->foreignId('catalogo_equipo_id')->nullable()->constrained('catalogo_equipos')->nullOnDelete()->after('compra_inventario_id');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->string('estatus')->default('PENDIENTE_ENTREGA')->after('nombre_lote');
            $table->foreignId('compra_inventario_id')->nullable()->constrained('compras_inventario')->nullOnDelete()->after('proveedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['compra_inventario_id']);
            $table->dropColumn(['estatus', 'compra_inventario_id']);
        });

        Schema::table('compras_inventario_items', function (Blueprint $table) {
            $table->dropForeign(['catalogo_equipo_id']);
            $table->dropColumn('catalogo_equipo_id');
        });

        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropForeign(['aprobado_por_id']);
            $table->dropColumn(['subtotal', 'iva', 'total', 'tipo_iva', 'aprobado_por_id', 'nombre_lote_propuesto']);
        });
    }
};
