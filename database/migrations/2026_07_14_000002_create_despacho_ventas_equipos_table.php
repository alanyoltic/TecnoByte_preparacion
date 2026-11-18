<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_ventas_equipos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('despacho_ventas_id')
                ->constrained('despachos_ventas')
                ->cascadeOnDelete();

            // El equipo físico (número de serie único)
            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->restrictOnDelete();

            // Precio sugerido por el encargado de Preparación
            // Se calcula al agregar (valor_unitario del lote) pero puede ajustarse
            $table->decimal('precio_sugerido', 10, 2)->nullable();

            // Nota individual por equipo (ej. "tiene rayón mínimo en tapa")
            $table->string('observacion', 500)->nullable();

            $table->timestamps();

            // Un equipo no puede estar en dos despachos activos (BORRADOR/ENVIADO) al mismo tiempo.
            // Se maneja a nivel aplicación; no se pone unique aquí porque un equipo rechazado
            // sí puede volver a despacharse en otro intento.
            $table->index(['despacho_ventas_id', 'equipo_id']);
            $table->index('equipo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_ventas_equipos');
    }
};
