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
        // Eliminar la tabla vieja con período
        Schema::dropIfExists('lider_modo_tecnico');

        // Crear la nueva tabla sin período: solo lider_id
        Schema::create('lider_modo_tecnico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lider_id')->unique()->constrained('users')->onDelete('cascade');
            $table->boolean('es_tecnico')->default(true)->comment('Si true, este líder trabaja como técnico en todas las métricas y meses');
            $table->foreignId('configurado_por_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('es_tecnico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lider_modo_tecnico');
    }
};
