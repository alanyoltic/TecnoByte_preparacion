<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_eliminaciones', function (Blueprint $table) {
            $table->id();

            // Identificadores clave del equipo eliminado (para buscar rápido)
            $table->string('numero_serie')->index();
            $table->string('codigo')->nullable()->index(); // si manejas código interno/barcode
            $table->string('tipo_equipo')->nullable()->index(); // opcional

            // Datos "humanos" (opcionales pero útiles)
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();

            // Motivo y actor
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('motivo', 255);

            // Snapshot del equipo (y opcionalmente relaciones)
            $table->json('snapshot')->nullable();

            // Metadata opcional
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_eliminaciones');
    }
};
