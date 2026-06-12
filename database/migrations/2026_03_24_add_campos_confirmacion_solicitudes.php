<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            // Agregar campos de confirmación (si no existen)
            if (! Schema::hasColumn('solicitudes_piezas', 'instalada')) {
                $table->boolean('instalada')->default(false)->after('notas_respuesta');
            }

            if (! Schema::hasColumn('solicitudes_piezas', 'funciono')) {
                $table->boolean('funciono')->nullable()->after('instalada');
            }

            if (! Schema::hasColumn('solicitudes_piezas', 'confirmada_en')) {
                $table->timestamp('confirmada_en')->nullable()->after('funciono');
            }

            if (! Schema::hasColumn('solicitudes_piezas', 'notas_confirmacion')) {
                $table->text('notas_confirmacion')->nullable()->after('confirmada_en');
            }

            // Agregar campo equipo_id para trazabilidad directa (opcional, compatible con asignacion_equipo_id)
            if (! Schema::hasColumn('solicitudes_piezas', 'equipo_id')) {
                $table->foreignId('equipo_id')
                    ->nullable()
                    ->after('asignacion_equipo_id')
                    ->constrained('equipos')
                    ->onDelete('cascade');
            }
        });

        // Agregar nuevo estado CONFIRMADA al ENUM
        DB::statement("ALTER TABLE solicitudes_piezas 
                       MODIFY COLUMN estatus ENUM(
                           'PENDIENTE',
                           'SURTIDA_INVENTARIO',
                           'PENDIENTE_COMPRA',
                           'COMPRADA',
                           'CANCELADA',
                           'CONFIRMADA'
                       ) NOT NULL DEFAULT 'PENDIENTE'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_piezas', 'equipo_id')) {
                $table->dropForeign(['equipo_id']);
                $table->dropColumn('equipo_id');
            }

            if (Schema::hasColumn('solicitudes_piezas', 'notas_confirmacion')) {
                $table->dropColumn('notas_confirmacion');
            }

            if (Schema::hasColumn('solicitudes_piezas', 'confirmada_en')) {
                $table->dropColumn('confirmada_en');
            }

            if (Schema::hasColumn('solicitudes_piezas', 'funciono')) {
                $table->dropColumn('funciono');
            }

            if (Schema::hasColumn('solicitudes_piezas', 'instalada')) {
                $table->dropColumn('instalada');
            }
        });

        // Revertir ENUM al original
        DB::statement("ALTER TABLE solicitudes_piezas 
                       MODIFY COLUMN estatus ENUM(
                           'PENDIENTE',
                           'SURTIDA_INVENTARIO',
                           'PENDIENTE_COMPRA',
                           'COMPRADA',
                           'CANCELADA'
                       ) NOT NULL DEFAULT 'PENDIENTE'");
    }
};
