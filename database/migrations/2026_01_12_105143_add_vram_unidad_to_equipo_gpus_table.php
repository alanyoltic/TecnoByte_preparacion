<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipo_gpus', function (Blueprint $table) {
            // Si tu vram es int o string, no importa: la unidad la separa
            $table->enum('vram_unidad', ['MB', 'GB'])->nullable()->after('vram');
            $table->index(['tipo', 'vram_unidad'], 'idx_gpus_tipo_unidad');
        });
    }

    public function down(): void
    {
        Schema::table('equipo_gpus', function (Blueprint $table) {
            $table->dropIndex('idx_gpus_tipo_unidad');
            $table->dropColumn('vram_unidad');
        });
    }
};
