<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('nombre')
                ->comment('Código de referencia / SKU para identificar el artículo');
            $table->string('codigo_barras')->nullable()->unique()->after('sku')
                ->comment('Código de barras para lectura por escáner');
        });
    }

    public function down(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['codigo_barras']);
            $table->dropColumn(['sku', 'codigo_barras']);
        });
    }
};
