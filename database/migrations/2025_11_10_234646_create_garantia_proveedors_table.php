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
        Schema::create('garantias_proveedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade')->unique();
            $table->enum('estatus_garantia', ['Pendiente de Envío', 'Enviada', 'Resuelta', 'Rechazada'])
                  ->default('Pendiente de Envío');
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantia_proveedors');
    }
};
