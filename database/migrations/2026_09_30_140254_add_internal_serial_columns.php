<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->string('codigo_interno')->nullable()->after('catalogo_pieza_id');
        });

        Schema::table('cargadores', function (Blueprint $table) {
            $table->string('serie_fabricante')->nullable()->after('serie');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_piezas', function (Blueprint $table) {
            $table->dropColumn('codigo_interno');
        });

        Schema::table('cargadores', function (Blueprint $table) {
            $table->dropColumn('serie_fabricante');
        });
    }
};
