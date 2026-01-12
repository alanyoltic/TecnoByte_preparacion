<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // ⚠️ Borra legacy
            $table->dropColumn([
                'grafica_integrada_modelo',
                'grafica_dedicada_modelo',
                'grafica_dedicada_vram',
                'tiene_tarjeta_dedicada',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // 🔙 rollback (tipos genéricos, ajusta si antes eran otros)
            $table->string('grafica_integrada_modelo')->nullable();
            $table->string('grafica_dedicada_modelo')->nullable();
            $table->string('grafica_dedicada_vram')->nullable();
            $table->boolean('tiene_tarjeta_dedicada')->default(false);
        });
    }
};
