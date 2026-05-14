<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Crear puntos_tecnicos ──────────────────────────────────────
        // Un registro por equipo terminado/parcial por técnico
        Schema::create('puntos_tecnicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tecnico_id')
                  ->constrained('users')
                  ->comment('Técnico que generó los puntos');

            $table->foreignId('asignacion_equipo_id')
                  ->constrained('asignacion_equipos')
                  ->cascadeOnDelete()
                  ->comment('Equipo trabajado');

            $table->foreignId('clasificacion_puntos_id')
                  ->nullable()
                  ->constrained('clasificaciones_puntos')
                  ->nullOnDelete()
                  ->comment('Clasificación A-F del equipo al momento de terminar');

            // ── Puntos ────────────────────────────────────────────────────
            $table->decimal('puntos_base', 5, 2)
                  ->comment('Puntos base de la clasificación (copiado al momento)');

            $table->decimal('porcentaje_aplicado', 5, 2)
                  ->default(100.00)
                  ->comment('% aplicado según camino: 100=completo, 40=inició pieza, 60=terminó pieza, 30=garantía');

            $table->decimal('puntos_final', 5, 2)
                  ->comment('Puntos reales = puntos_base * (porcentaje_aplicado / 100) + ajuste_manual');

            $table->decimal('ajuste_manual', 5, 2)
                  ->default(0)
                  ->comment('Ajuste del gerente, puede ser positivo o negativo');

            $table->foreignId('ajustado_por_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Gerente que aplicó el ajuste manual');

            $table->text('motivo_ajuste')->nullable();

            // ── Contexto del camino ───────────────────────────────────────
            $table->enum('rol_en_equipo', [
                'COMPLETO',           // Tech completó todo el equipo
                'PIEZA_PENDIENTE',    // Tech terminó equipo pero falta pieza
                'PIEZA_COMPLETADA',   // Tech completó la instalación de la pieza
                'GARANTIA',           // Tech detectó falla y mandó a garantía
                'DESPIECE',           // Tech mandó equipo a despiece
            ])->comment('Qué rol tuvo el técnico en este equipo');

            // ── Período para agrupación de reportes ───────────────────────
            $table->char('periodo', 7)
                  ->comment('Año-Mes en formato YYYY-MM, ej: 2026-03');

            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────────
            $table->index('tecnico_id');
            $table->index('periodo');
            $table->index(['tecnico_id', 'periodo']);
            $table->index('rol_en_equipo');
        });

        // ── 2. Crear metas_tecnicos ───────────────────────────────────────
        // Meta mensual por técnico (actualmente 140 puntos para todos,
        // pero puede variar por técnico en el futuro)
        Schema::create('metas_tecnicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tecnico_id')
                  ->constrained('users')
                  ->comment('Técnico al que aplica la meta');

            $table->char('periodo', 7)
                  ->comment('YYYY-MM');

            $table->decimal('meta_puntos', 6, 2)
                  ->default(140.00)
                  ->comment('Meta de puntos para ese período');

            $table->foreignId('asignada_por_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Gerente que asignó la meta');

            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['tecnico_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_tecnicos');
        Schema::dropIfExists('puntos_tecnicos');
    }
};
