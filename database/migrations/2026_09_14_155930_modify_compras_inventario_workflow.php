<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('almacen_destino_id')->nullable()->change();
            
            $table->foreignId('aprobado_gerente_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion_gerente')->nullable();
            
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cancelacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropForeign(['aprobado_gerente_por_id']);
            $table->dropColumn(['aprobado_gerente_por_id', 'fecha_aprobacion_gerente']);
            
            $table->dropForeign(['cancelado_por_id']);
            $table->dropColumn(['cancelado_por_id', 'fecha_cancelacion']);
        });
    }
};
