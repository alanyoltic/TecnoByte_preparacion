<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            // Timestamp de cuándo fue rechazado
            $table->timestamp('rechazado_en')->nullable()->after('fin_en');

            // Contador de intentos/rechazos
            $table->unsignedTinyInteger('num_rechazos')->default(0)->after('rechazado_en');
        });
    }

    public function down(): void
    {
        Schema::table('asignacion_equipos', function (Blueprint $table) {
            $table->dropColumn(['rechazado_en', 'num_rechazos']);
        });
    }
};
