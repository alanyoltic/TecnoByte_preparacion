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
            $table->decimal('valor_unitario', 10, 2)
                  ->nullable()
                  ->after('cantidad_recibida')
                  ->comment('Precio unitario por equipo de este modelo en el lote');
        });
    }

    public function down(): void
    {
        Schema::table('lote_modelos_recibidos', function (Blueprint $table) {
            $table->dropColumn('valor_unitario');
        });
    }
};
