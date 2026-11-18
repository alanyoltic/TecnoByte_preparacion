<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_ventas_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_ventas_id')->constrained('despachos_ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_sugerido', 10, 2)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_ventas_productos');
    }
};
