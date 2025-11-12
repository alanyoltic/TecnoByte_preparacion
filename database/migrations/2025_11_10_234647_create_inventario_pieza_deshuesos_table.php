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
        Schema::create('inventario_piezas_deshueso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pieza_id')->constrained('catalogo_piezas');
            $table->foreignId('equipo_origen_id')
                  ->nullable()
                  ->constrained('equipos')
                  ->onDelete('set null');
            $table->enum('estatus', ['Disponible', 'Dañada', 'Instalada'])->default('Disponible');
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_pieza_deshuesos');
    }
};
