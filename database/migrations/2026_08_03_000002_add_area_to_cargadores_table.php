<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargadores', function (Blueprint $table) {
            $table->string('area', 30)->default('PREPARACION')
                  ->comment('PREPARACION | VENTAS | ADMIN | ...')
                  ->after('estatus');
        });

        // Todos los cargadores existentes son de Preparación
        DB::table('cargadores')->update(['area' => 'PREPARACION']);
    }

    public function down(): void
    {
        Schema::table('cargadores', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
