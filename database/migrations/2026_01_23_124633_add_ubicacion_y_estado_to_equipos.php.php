<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('id')->constrained('sucursales')->nullOnDelete();
            $table->foreignId('almacen_id')->nullable()->after('sucursal_id')->constrained('almacenes')->nullOnDelete();

            $table->enum('estado_operativo', [
                'PENDIENTE_PREPARACION',
                'EN_PREPARACION',
                'EN_QA',
                'EN_VENTAS',
                'APARTADO',
                'VENDIDO',
                'BAJA',
            ])->default('PENDIENTE_PREPARACION')->after('almacen_id');

            $table->index(['sucursal_id', 'almacen_id', 'estado_operativo']);
        });

        // Backfill suave:
        // - asigna sucursal QRO a equipos existentes
        // - asigna almacen QRO_CEDIS_PREP a equipos existentes
        // - NO adivinamos estados legacy: dejamos default PENDIENTE_PREPARACION, y tú decides luego si quieres mover masivo
        $qroId = DB::table('sucursales')->where('clave', 'QRO')->value('id');
        $cedisId = DB::table('almacenes')->where('clave', 'QRO_CEDIS_PREP')->value('id');

        if ($qroId && $cedisId) {
            DB::table('equipos')
                ->whereNull('sucursal_id')
                ->update(['sucursal_id' => $qroId]);

            DB::table('equipos')
                ->whereNull('almacen_id')
                ->update(['almacen_id' => $cedisId]);
        }
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex(['sucursal_id', 'almacen_id', 'estado_operativo']);
            $table->dropColumn('estado_operativo');
            $table->dropConstrainedForeignId('almacen_id');
            $table->dropConstrainedForeignId('sucursal_id');
        });
    }
};
