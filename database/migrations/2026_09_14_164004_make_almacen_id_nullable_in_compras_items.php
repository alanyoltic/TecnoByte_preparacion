<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_inventario_items', function (Blueprint $table) {
            $table->unsignedBigInteger('almacen_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compras_inventario_items', function (Blueprint $table) {
            // Not safely reversible if there are nulls, but we can leave it
            $table->unsignedBigInteger('almacen_id')->nullable(false)->change();
        });
    }
};
