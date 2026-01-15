<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('lectores_microsd', 50)->nullable()->after('lectores_sd');
            $table->string('lectores_otro', 255)->nullable()->after('lectores_sim');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['lectores_microsd', 'lectores_otro']);
        });
    }
};
