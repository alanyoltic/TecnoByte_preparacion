<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('sku')->nullable()->unique()
                ->comment('Código interno / referencia del producto');
            $table->string('codigo_barras')->nullable()->unique()
                ->comment('Código de barras para lectura por escáner');
            $table->string('nombre');
            $table->string('categoria')->nullable()
                ->comment('ACCESORIO | CABLE | PERIFÉRICO | BOLSA | LIMPIEZA | OTRO');
            $table->string('marca')->nullable();
            $table->text('descripcion')->nullable();

            // Precios comerciales
            $table->decimal('precio_compra', 10, 2)->nullable()
                ->comment('Costo de adquisición');
            $table->decimal('precio_venta', 10, 2)->nullable()
                ->comment('Precio de venta al público');

            // Control
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
