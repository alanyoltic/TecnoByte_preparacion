<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipo_piezas_faltantes', function (Blueprint $table) {
            // Ligar con la solicitud operativa
            $table->foreignId('solicitud_pieza_id')
                  ->nullable()
                  ->after('estatus_pieza')
                  ->constrained('solicitudes_piezas')
                  ->nullOnDelete()
                  ->comment('Solicitud generada para conseguir esta pieza');

            // Por si la pieza no está en catálogo
            $table->string('descripcion_libre', 255)
                  ->nullable()
                  ->after('solicitud_pieza_id')
                  ->comment('Descripción libre si la pieza no está en catálogo');

            // Auditoría
            $table->foreignId('registrado_por_id')
                  ->nullable()
                  ->after('descripcion_libre')
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Técnico que registró la pieza faltante');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('equipo_piezas_faltantes', function (Blueprint $table) {
            $table->dropForeign(['solicitud_pieza_id']);
            $table->dropForeign(['registrado_por_id']);
            $table->dropColumn([
                'solicitud_pieza_id',
                'descripcion_libre',
                'registrado_por_id',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
