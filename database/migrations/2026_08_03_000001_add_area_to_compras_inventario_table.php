<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->string('area', 30)->default('PREPARACION')
                  ->comment('PREPARACION | VENTAS | ADMIN | ...')
                  ->after('registrado_por_id');
        });

        // Todos los registros existentes son de Preparación
        DB::table('compras_inventario')->update(['area' => 'PREPARACION']);
    }

    public function down(): void
    {
        Schema::table('compras_inventario', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
