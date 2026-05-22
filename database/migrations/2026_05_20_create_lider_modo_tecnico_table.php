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
        Schema::create('lider_modo_tecnico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lider_id')->constrained('users')->onDelete('cascade');
            $table->string('periodo')->comment('Formato Y-m, ej: 2026-05');
            $table->boolean('activo')->default(true)->comment('Si true, el líder cuenta como técnico en metas y métricas para este período');
            $table->foreignId('configurado_por_id')->nullable()->constrained('users')->onDelete('set null')->comment('Quién habilitó/deshabilitó este registro');
            $table->text('notas')->nullable()->comment('Notas sobre por qué el líder trabaja/no trabaja este mes');
            $table->timestamps();

            // Índices
            $table->unique(['lider_id', 'periodo'], 'unique_lider_periodo');
            $table->index('periodo');
            $table->index('activo');
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
