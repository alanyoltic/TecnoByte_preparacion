<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrada_producto_items', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('entrada_producto_id')->constrained('entradas_productos')->cascadeOnDelete();
            
            // Producto recibido
            $table->foreignId('producto_id')->constrained('productos');
            
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2)->nullable();
            
            // Almacen donde se guardará (generalmente Almacén Ventas)
            $table->foreignId('almacen_id')->constrained('almacenes');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_producto_items');
    }
};
