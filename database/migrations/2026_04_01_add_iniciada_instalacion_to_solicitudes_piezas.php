<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->timestamp('iniciada_instalacion_en')
                  ->nullable()
                  ->after('reasignada_en');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropColumn('iniciada_instalacion_en');
        });
    }
};
