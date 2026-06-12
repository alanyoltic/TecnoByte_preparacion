<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_pieza_intentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_pieza_id')->constrained('solicitudes_piezas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_intento')->default(1);
            $table->foreignId('inventario_pieza_id')->nullable()->constrained('inventario_piezas')->nullOnDelete();
            $table->foreignId('asignado_a_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asignado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asignado_en')->nullable();
            $table->decimal('puntos_override', 8, 2)->nullable();
            $table->text('notas_asignacion')->nullable();
            $table->timestamp('iniciada_instalacion_en')->nullable();
            $table->timestamp('confirmada_en')->nullable();
            $table->boolean('funciono')->nullable();
            $table->text('notas_confirmacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_pieza_intentos');
    }
};
