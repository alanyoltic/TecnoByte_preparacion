<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_auditorias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('accion', 30); // EDITADO, ELIMINADO, RESTAURADO, REASIGNADO, CREADO
            $table->string('motivo', 255)->nullable();

            // Solo lo que cambió: { campo: {antes: X, despues: Y}, ... }
            $table->json('cambios')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->index(['equipo_id', 'created_at']);
            $table->index(['accion', 'created_at']);

            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_auditorias');
    }
};
