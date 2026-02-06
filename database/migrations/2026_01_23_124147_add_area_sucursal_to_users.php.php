<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable por compatibilidad con tus datos actuales
            $table->foreignId('area_id')->nullable()->after('role_id')->constrained('areas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->after('area_id')->constrained('sucursales')->nullOnDelete();

            $table->index(['area_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['area_id', 'sucursal_id']);
            $table->dropConstrainedForeignId('sucursal_id');
            $table->dropConstrainedForeignId('area_id');
        });
    }
};
