<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipo_eliminaciones', function (Blueprint $table) {
            $table->string('accion', 40)->default('ELIMINACION')->after('id');
            $table->unsignedBigInteger('equipo_id_original')->nullable()->after('accion');

            $table->index('accion');
            $table->index('equipo_id_original');
        });
    }

    public function down(): void
    {
        Schema::table('equipo_eliminaciones', function (Blueprint $table) {
            $table->dropIndex(['accion']);
            $table->dropIndex(['equipo_id_original']);
            $table->dropColumn(['accion', 'equipo_id_original']);
        });
    }
};
