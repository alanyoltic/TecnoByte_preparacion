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
        Schema::create('garantias_clientes', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('restrict');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('restrict');
            $table->foreignId('reportado_por_id')->constrained('users')->onDelete('restrict'); // Usuario que recibe
            
            $table->foreignId('almacen_origen_id')->constrained('almacenes')->onDelete('restrict'); // Almacén de Ventas
            
            // Si genera un despacho hacia preparacion
            $table->foreignId('despacho_retorno_id')->nullable()->constrained('despachos_ventas')->onDelete('set null');

            // Estatus: PENDIENTE_RECEPCION (en CEDIS), EN_REVISION, REPARADO, REEMPLAZADO, RECHAZADO, ENTREGADO
            $table->string('estatus')->default('PENDIENTE_CEDIS');

            $table->text('falla_reportada');
            $table->text('condiciones_recepcion')->nullable(); // Ej. Viene con cargador, rayones, etc.
            
            $table->text('resolucion_tecnica')->nullable();
            
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamp('fecha_entrega_cliente')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias_clientes');
    }
};
