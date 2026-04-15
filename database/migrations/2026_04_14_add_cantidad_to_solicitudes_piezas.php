<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->unsignedTinyInteger('cantidad')
                ->default(1)
                ->after('descripcion_libre')
                ->comment('Cantidad de unidades de esta pieza que necesita el técnico');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};
