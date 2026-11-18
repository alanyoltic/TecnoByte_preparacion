<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliar tabla sucursales con identidad física, fiscal y responsable
        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('nombre');
            $table->string('colonia')->nullable()->after('direccion');
            $table->string('codigo_postal', 10)->nullable()->after('colonia');
            $table->string('ciudad')->nullable()->after('codigo_postal');
            $table->string('estado')->nullable()->after('ciudad');
            $table->string('telefono', 50)->nullable()->after('estado');
            $table->boolean('es_matriz')->default(false)->after('es_virtual');
            $table->foreignId('encargado_id')->nullable()->after('es_matriz')->constrained('users')->nullOnDelete();
        });

        // 2. Agregar sucursal_id directo a la tabla ventas para reportes instantáneos
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('almacen_id')->constrained('sucursales')->nullOnDelete();
        });

        // 3. Poblar retrospectivamente sucursal_id en ventas existentes a partir de su almacén
        DB::statement("
            UPDATE ventas v
            JOIN almacenes a ON v.almacen_id = a.id
            SET v.sucursal_id = a.sucursal_id
            WHERE v.sucursal_id IS NULL AND a.sucursal_id IS NOT NULL
        ");

        // 4. Marcar Querétaro (QRO) como matriz por defecto
        DB::table('sucursales')
            ->where('clave', 'QRO')
            ->orWhere('clave', 'QRO_CORREGIDORA')
            ->limit(1)
            ->update(['es_matriz' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropForeign(['encargado_id']);
            $table->dropColumn([
                'direccion',
                'colonia',
                'codigo_postal',
                'ciudad',
                'estado',
                'telefono',
                'es_matriz',
                'encargado_id',
            ]);
        });
    }
};
