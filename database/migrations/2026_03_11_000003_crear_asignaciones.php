<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();

            // ── Quién asigna y a quién ────────────────────────────────────
            $table->foreignId('tecnico_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Técnico al que se le asigna el equipo');

            $table->foreignId('asignado_por_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Usuario que realizó la asignación (líder/gerente)');

            // ── Qué equipo ────────────────────────────────────────────────
            $table->foreignId('equipo_id')
                  ->constrained('equipos')
                  ->cascadeOnDelete()
                  ->comment('Equipo asignado');

            // ── Fechas ────────────────────────────────────────────────────
            $table->date('fecha_asignacion')->comment('Fecha en que se asignó el equipo');
            $table->date('fecha_entrega')->nullable()->comment('Fecha en que el técnico entregó el equipo terminado');

            // ── Estado de la asignación ───────────────────────────────────
            $table->enum('estatus', [
                'PENDIENTE',    // Asignado, aún no lo trabaja
                'EN_PROCESO',   // El técnico lo está trabajando
                'ENTREGADO',    // Técnico lo entregó, pendiente validar
                'VALIDADO',     // Líder/gerente validó el trabajo
                'CANCELADO',    // Se canceló la asignación
            ])->default('PENDIENTE');

            // ── Notas ─────────────────────────────────────────────────────
            $table->text('notas')->nullable()->comment('Instrucciones especiales o comentarios');
            $table->text('notas_entrega')->nullable()->comment('Notas del técnico al entregar');

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────────
            $table->index('tecnico_id');
            $table->index('equipo_id');
            $table->index('estatus');
            $table->index('fecha_asignacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
