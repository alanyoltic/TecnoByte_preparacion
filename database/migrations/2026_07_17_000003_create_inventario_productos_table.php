<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('almacen_id')
                ->constrained('almacenes')
                ->cascadeOnDelete();

            $table->unsignedInteger('cantidad')->default(0);

            $table->timestamps();

            // Un producto tiene una sola fila de stock por almacén
            $table->unique(['producto_id', 'almacen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_productos');
    }
};
