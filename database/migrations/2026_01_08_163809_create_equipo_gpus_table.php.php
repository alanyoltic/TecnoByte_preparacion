<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_gpus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->cascadeOnDelete();

            // integrada | dedicada
            $table->string('tipo', 20);

            // Para conteo/estado sin soft deletes
            $table->boolean('activo')->default(true);

            // Datos
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();

            // Memoria (VRAM) opcional. En integrada puedes dejar null.
            $table->unsignedSmallInteger('vram_gb')->nullable();

            // Si luego quieres: notas/observaciones
            $table->string('notas', 255)->nullable();

            $table->timestamps();

            // Evitar duplicados por tipo (solo 1 integrada y 1 dedicada por equipo)
            $table->unique(['equipo_id', 'tipo']);
            $table->index(['equipo_id', 'tipo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_gpus');
    }
};
