<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('puntos_tecnicos', function (Blueprint $table) {
            $table->string('estatus', 30)->default('APROBADO')->after('periodo')->index();
            $table->timestamp('aprobado_en')->nullable()->after('estatus');
        });

        // Asegurar que todos los registros históricos existentes queden APROBADOS con su fecha de creación
        DB::table('puntos_tecnicos')->update([
            'estatus' => 'APROBADO',
            'aprobado_en' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('puntos_tecnicos', function (Blueprint $table) {
            $table->dropColumn(['estatus', 'aprobado_en']);
        });
    }
};
