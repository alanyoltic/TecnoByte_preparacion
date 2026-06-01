<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->boolean('requiere_cable_bateria')
                ->default(false)
                ->after('cantidad')
                ->comment('Indica si se requiere cable junto con la batería');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropColumn('requiere_cable_bateria');
        });
    }
};
