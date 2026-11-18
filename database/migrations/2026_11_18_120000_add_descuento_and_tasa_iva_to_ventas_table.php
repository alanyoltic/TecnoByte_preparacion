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
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('tipo_descuento', 15)->default('MONTO')->after('subtotal');
            $table->decimal('valor_descuento', 10, 2)->default(0.00)->after('tipo_descuento');
            $table->decimal('descuento', 10, 2)->default(0.00)->after('valor_descuento');
            $table->decimal('tasa_iva', 5, 2)->default(0.00)->after('descuento');
            $table->boolean('aplica_iva')->default(false)->after('tasa_iva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_descuento',
                'valor_descuento',
                'descuento',
                'tasa_iva',
                'aplica_iva',
            ]);
        });
    }
};
