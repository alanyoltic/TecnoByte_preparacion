<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->foreignId('reasignado_a_id')
                ->nullable()
                ->after('respondida_por_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reasignada_en')->nullable()->after('reasignado_a_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropForeign(['reasignado_a_id']);
            $table->dropColumn(['reasignado_a_id', 'reasignada_en']);
        });
    }
};
