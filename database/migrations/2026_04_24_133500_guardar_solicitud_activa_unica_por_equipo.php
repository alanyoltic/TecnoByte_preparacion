<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTIVE_STATUSES = [
        'PENDIENTE',
        'PENDIENTE_COMPRA',
        'COMPRADA',
        'SURTIDA_INVENTARIO',
        'REQUIERE_REASIGNACION',
    ];

    public function up(): void
    {
        // Normaliza equipo_id en registros legacy para poder aplicar unicidad por equipo.
        DB::statement('
            UPDATE solicitudes_piezas sp
            INNER JOIN asignacion_equipos ae ON ae.id = sp.asignacion_equipo_id
            SET sp.equipo_id = ae.equipo_id
            WHERE sp.equipo_id IS NULL
        ');

        $this->cancelarDuplicadasActivas();

        if (! Schema::hasColumn('solicitudes_piezas', 'active_equipo_id')) {
            DB::statement("
                ALTER TABLE solicitudes_piezas
                ADD COLUMN active_equipo_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (
                    CASE
                        WHEN estatus IN ('PENDIENTE','PENDIENTE_COMPRA','COMPRADA','SURTIDA_INVENTARIO','REQUIERE_REASIGNACION')
                        THEN equipo_id
                        ELSE NULL
                    END
                ) STORED
            ");
        }

        if (! $this->tieneIndice('solicitudes_piezas', 'solicitudes_piezas_active_equipo_unique')) {
            DB::statement('
                ALTER TABLE solicitudes_piezas
                ADD UNIQUE INDEX solicitudes_piezas_active_equipo_unique (active_equipo_id)
            ');
        }
    }

    public function down(): void
    {
        if ($this->tieneIndice('solicitudes_piezas', 'solicitudes_piezas_active_equipo_unique')) {
            DB::statement('
                ALTER TABLE solicitudes_piezas
                DROP INDEX solicitudes_piezas_active_equipo_unique
            ');
        }

        if (Schema::hasColumn('solicitudes_piezas', 'active_equipo_id')) {
            DB::statement('
                ALTER TABLE solicitudes_piezas
                DROP COLUMN active_equipo_id
            ');
        }
    }

    private function cancelarDuplicadasActivas(): void
    {
        $duplicadasPorEquipo = DB::table('solicitudes_piezas')
            ->select('equipo_id', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('equipo_id')
            ->whereIn('estatus', self::ACTIVE_STATUSES)
            ->groupBy('equipo_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('equipo_id');

        foreach ($duplicadasPorEquipo as $equipoId) {
            $idsActivos = DB::table('solicitudes_piezas')
                ->where('equipo_id', $equipoId)
                ->whereIn('estatus', self::ACTIVE_STATUSES)
                ->orderByDesc('id')
                ->pluck('id');

            $idConservado = $idsActivos->shift();
            if (! $idConservado || $idsActivos->isEmpty()) {
                continue;
            }

            DB::table('solicitudes_piezas')
                ->whereIn('id', $idsActivos->all())
                ->update([
                    'estatus' => 'CANCELADA',
                    'respondida_en' => now(),
                    'notas_respuesta' => DB::raw(
                        "TRIM(CONCAT(IFNULL(notas_respuesta, ''), ' | Auto-cancelada por migracion: solicitud activa duplicada del mismo equipo.'))"
                    ),
                    'updated_at' => now(),
                ]);
        }
    }

    private function tieneIndice(string $table, string $indexName): bool
    {
        $res = DB::select(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$indexName]
        );

        return ! empty($res);
    }
};
