<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validaciones_calidad', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('asignacion_equipo_id');
            $table->unsignedBigInteger('validado_por_user_id');

            // Estado de la validación
            $table->enum('estado', ['APROBADO', 'RECHAZADO'])->default('APROBADO');

            // Calificación general (1-5, nullable)
            $table->unsignedTinyInteger('calificacion_general')->nullable();

            // Checklists en JSON
            $table->json('checklist_qué_salió_bien')->nullable();  // Array de strings
            $table->json('checklist_qué_salió_mal')->nullable();   // Array de strings

            // Motivo (obligatorio si RECHAZADO)
            $table->text('motivo')->nullable();

            // Notas adicionales
            $table->text('notas_adicionales')->nullable();

            // Auditoría
            $table->timestamps();

            // Foreign keys
            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('asignacion_equipo_id')->references('id')->on('asignacion_equipos')->onDelete('cascade');
            $table->foreign('validado_por_user_id')->references('id')->on('users')->onDelete('restrict');

            // Índices para búsquedas frecuentes
            $table->index('equipo_id');
            $table->index('estado');
            $table->index('validado_por_user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validaciones_calidad');
    }
};
