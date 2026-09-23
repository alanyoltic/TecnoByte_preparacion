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
        Schema::table('consumibles', function (Blueprint $table) {
            $table->string('area', 50)->default('AMBAS')->after('categoria')
                  ->comment('PREPARACION, VENTAS, AMBAS u otras en el futuro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
