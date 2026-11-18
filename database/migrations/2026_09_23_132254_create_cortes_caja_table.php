<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('restrict');
            $table->decimal('fondo_inicial', 10, 2)->default(0);
            $table->decimal('total_ventas_efectivo', 10, 2)->default(0);
            $table->decimal('total_ventas_tarjeta', 10, 2)->default(0);
            $table->decimal('total_ventas_transferencia', 10, 2)->default(0);
            $table->decimal('total_calculado', 10, 2)->default(0);
            $table->decimal('total_declarado', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->string('estatus')->default('ABIERTA'); // ABIERTA, CERRADA
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('corte_caja_id')->nullable()->constrained('cortes_caja')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['corte_caja_id']);
            $table->dropColumn('corte_caja_id');
        });
        
        Schema::dropIfExists('cortes_caja');
    }
};
