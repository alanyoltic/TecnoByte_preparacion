<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ajustar tabla asignaciones ─────────────────────────────────
        // Quitar equipo_id (era individual) y agregar lote_modelo_id + cantidad
        // porque la asignación es genérica: "Juan, 10 L15 del lote 480UBTB"
        Schema::table('asignaciones', function (Blueprint $table) {
            // Quitar FK e índice de equipo_id
            $table->dropForeign(['equipo_id']);
            $table->dropIndex(['equipo_id']);
            $table->dropColumn('equipo_id');

            // Agregar referencia al modelo del lote
            $table->foreignId('lote_modelo_id')
                  ->after('asignado_por_id')
                  ->constrained('lote_modelos_recibidos')
                  ->cascadeOnDelete()
                  ->comment('Modelo específico del lote a preparar');

            // Cantidad de equipos asignados de ese modelo
            $table->unsignedSmallInteger('cantidad')
                  ->after('lote_modelo_id')
                  ->comment('Cuántos equipos de ese modelo se asignan');

            $table->index('lote_modelo_id');
        });

        // ── 2. Crear tabla asignacion_equipos ─────────────────────────────
        // Detalle individual: cuando el técnico escanea el número de serie
        // y empieza a trabajar en un equipo específico
        Schema::create('asignacion_equipos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asignacion_id')
                  ->constrained('asignaciones')
                  ->cascadeOnDelete()
                  ->comment('Asignación genérica a la que pertenece');

            $table->foreignId('equipo_id')
                  ->constrained('equipos')
                  ->cascadeOnDelete()
                  ->comment('Equipo específico (escaneado por el técnico)');

            // ── Tiempos ───────────────────────────────────────────────────
            $table->timestamp('inicio_en')->nullable()->comment('Cuando el técnico empieza a trabajarlo');
            $table->timestamp('fin_en')->nullable()->comment('Cuando el técnico lo marca como terminado');

            // ── Camino que tomó el equipo ─────────────────────────────────
            $table->enum('camino', [
                'EN_PROCESO',       // Aún trabajándose
                'COMPLETADO',       // Todo bien, pasa a calidad
                'PIEZA_PENDIENTE',  // Le falta una pieza
                'GARANTIA_INTERNA', // Falla cubierta por nosotros
                'GARANTIA_EXTERNA', // Falla cubierta por proveedor
            ])->default('EN_PROCESO');

            // ── Notas del técnico ─────────────────────────────────────────
            $table->text('notas')->nullable()->comment('Observaciones del técnico al terminar');

            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────────
            $table->index('asignacion_id');
            $table->index('equipo_id');
            $table->index('camino');
            $table->unique('equipo_id'); // Un equipo solo puede estar en una asignación activa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_equipos');

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropForeign(['lote_modelo_id']);
            $table->dropIndex(['lote_modelo_id']);
            $table->dropColumn(['lote_modelo_id', 'cantidad']);

            $table->foreignId('equipo_id')
                  ->constrained('equipos')
                  ->cascadeOnDelete();
        });
    }
};
