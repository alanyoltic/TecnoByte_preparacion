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
        Schema::table('lote_modelos_recibidos', function (Blueprint $table) {
            $table->foreignId('catalogo_equipo_id')
                  ->nullable()
                  ->after('lote_id')
                  ->constrained('catalogo_equipos')
                  ->onDelete('set null');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('catalogo_equipo_id')
                  ->nullable()
                  ->after('lote_modelo_id')
                  ->constrained('catalogo_equipos')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['catalogo_equipo_id']);
            $table->dropColumn('catalogo_equipo_id');
        });

        Schema::table('lote_modelos_recibidos', function (Blueprint $table) {
            $table->dropForeign(['catalogo_equipo_id']);
            $table->dropColumn('catalogo_equipo_id');
        });
    }
};
