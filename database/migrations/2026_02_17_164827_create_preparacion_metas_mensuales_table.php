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
        Schema::create('preparacion_metas_mensuales', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');

            $table->unsignedInteger('tecnicos_iniciales');
            $table->unsignedInteger('meta_total');

            $table->boolean('hubo_movimientos')->default(false);

            $table->timestamps();

            $table->unique(['anio', 'mes']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preparacion_metas_mensuales');
    }
};
