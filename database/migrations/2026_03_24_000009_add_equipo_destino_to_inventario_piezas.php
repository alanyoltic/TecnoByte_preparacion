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
        Schema::table('inventario_piezas', function (Blueprint $table) {
            // Campo para vincular la pieza al equipo donde se instaló
            $table->foreignId('equipo_destino_id')
                  ->nullable()
                  ->after('equipo_origen_id')
                  ->constrained('equipos')
                  ->onDelete('set null');
            
            // Agregar estado RESERVADA si no existe
            // Si ya tienes una columna estado, puedes ignorar esta línea
            // o ajustar los valores del enum según tu caso
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->dropForeign(['equipo_destino_id']);
            $table->dropColumn('equipo_destino_id');
        });
    }
};
