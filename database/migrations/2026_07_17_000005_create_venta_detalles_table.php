<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // Relación polimórfica: App\Models\Equipo | App\Models\Producto
            // (Consumibles NUNCA aparecen aquí — son uso interno)
            $table->morphs('vendible');

            $table->unsignedInteger('cantidad')->default(1)
                ->comment('Siempre 1 para Equipos. N para Productos.');

            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
    }
};
