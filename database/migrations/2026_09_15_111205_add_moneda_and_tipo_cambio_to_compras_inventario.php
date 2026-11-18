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
            $table->string('moneda', 3)->default('MXN')->after('total');
            $table->decimal('tipo_cambio', 10, 4)->nullable()->after('moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropColumn(['moneda', 'tipo_cambio']);
        });
    }
};
