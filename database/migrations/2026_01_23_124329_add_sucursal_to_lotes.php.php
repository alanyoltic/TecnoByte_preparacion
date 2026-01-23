<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('proveedor_id')->constrained('sucursales')->nullOnDelete();
        });

        // El default real lo metemos vía seeder (QRO), y aquí solo dejamos columna lista.
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sucursal_id');
        });
    }
};
