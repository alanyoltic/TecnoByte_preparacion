<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_modelos_recibidos', function (Blueprint $table) {
            $table->foreignId('clasificacion_puntos_id')
                ->nullable()
                ->after('cantidad_recibida')
                ->constrained('clasificaciones_puntos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lote_modelos_recibidos', function (Blueprint $table) {
            $table->dropForeign(['clasificacion_puntos_id']);
            $table->dropColumn('clasificacion_puntos_id');
        });
    }
};
