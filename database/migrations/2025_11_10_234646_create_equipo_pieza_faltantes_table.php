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
        Schema::create('equipo_piezas_faltantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->foreignId('pieza_id')->constrained('catalogo_piezas')->onDelete('cascade');
            $table->integer('cantidad')->unsigned()->default(1);
            $table->enum('estatus_pieza', ['Pendiente Compra', 'Comprada', 'Instalada', 'Cancelada'])
                  ->default('Pendiente Compra');
            $table->unique(['equipo_id', 'pieza_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_pieza_faltantes');
    }
};
