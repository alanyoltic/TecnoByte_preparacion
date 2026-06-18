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
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            $table->dropUnique(['equipo_id']);
            // El índice original que tenía no se borró, pero para estar seguros agregamos uno normal si no existía.
            // La migración original hacía $table->index('equipo_id') antes del unique, pero al hacer dropUnique,
            // el índice subyacente puede quedarse. Para evitar errores, no creamos uno nuevo explícitamente a menos que falte.
            // O mejor aún, creamos un índice explícito con un nombre custom por si acaso.
            $table->index('equipo_id', 'asignacion_equipos_equipo_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            $table->dropIndex('asignacion_equipos_equipo_id_idx');
            $table->unique('equipo_id');
        });
    }
};
