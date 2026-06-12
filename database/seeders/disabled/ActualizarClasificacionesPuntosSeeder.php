<?php

namespace Database\Seeders\Disabled;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActualizarClasificacionesPuntosSeeder extends Seeder
{
    /**
     * Actualiza SOLO los valores de clasificaciones de puntos y recalcula puntos históricos.
     * - Actualiza clasificaciones existentes con nuevos valores
     * - Recalcula puntos_final en puntos_tecnicos basado en los nuevos valores
     * - NO crea clasificaciones para equipos sin ellas
     *
     * Valores correctos:
     * A = 1.0, B = 1.4, C = 1.2, D = 1.4, E = 1.8, F = 0.5
     */
    public function run(): void
    {
        $this->command->info('Iniciando actualización de valores de clasificaciones y puntos históricos...');

        // =====================================================================
        // 1. ACTUALIZAR CLASIFICACIONES_PUNTOS
        // =====================================================================
        $clasificaciones = [
            'A' => ['puntos' => 1.0, 'nombre' => 'Básico'],
            'B' => ['puntos' => 1.4, 'nombre' => 'Estándar'],
            'C' => ['puntos' => 1.2, 'nombre' => 'Intermedio'],
            'D' => ['puntos' => 1.4, 'nombre' => 'Avanzado'],
            'E' => ['puntos' => 1.8, 'nombre' => 'Premium'],
            'F' => ['puntos' => 0.5, 'nombre' => 'Deshueso'],
        ];

        foreach ($clasificaciones as $clave => $datos) {
            $existe = DB::table('clasificaciones_puntos')
                ->where('clave', $clave)
                ->first();

            if ($existe) {
                DB::table('clasificaciones_puntos')
                    ->where('clave', $clave)
                    ->update([
                        'puntos_base' => $datos['puntos'],
                        'nombre' => $datos['nombre'],
                        'activo' => true,
                    ]);
                $this->command->info("✓ Clasificación {$clave} actualizada: {$datos['puntos']} puntos");
            } else {
                DB::table('clasificaciones_puntos')->insert([
                    'clave' => $clave,
                    'nombre' => $datos['nombre'],
                    'descripcion' => "Clasificación {$clave}",
                    'puntos_base' => $datos['puntos'],
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("✓ Clasificación {$clave} creada: {$datos['puntos']} puntos");
            }
        }

        // =====================================================================
        // 2. RECALCULAR PUNTOS_FINAL EN PUNTOS_TECNICOS
        // =====================================================================
        $this->command->info("\nRecalculando puntos históricos con nuevos valores...");

        $puntosActualizados = DB::statement(
            'UPDATE puntos_tecnicos pt
            INNER JOIN clasificaciones_puntos cp ON pt.clasificacion_puntos_id = cp.id
            SET pt.puntos_final = ROUND(pt.puntos_base * (pt.porcentaje_aplicado / 100), 2),
                pt.updated_at = NOW()
            WHERE pt.clasificacion_puntos_id IS NOT NULL'
        );

        $totalPuntosActualizados = DB::table('puntos_tecnicos')
            ->whereNotNull('clasificacion_puntos_id')
            ->count();

        $this->command->info("✓ Recalculados {$totalPuntosActualizados} registros de puntos");

        // =====================================================================
        // 3. REPORT FINAL
        // =====================================================================
        $totalEquiposConClasificacion = DB::table('equipos')
            ->whereNotNull('clasificacion_puntos_id')
            ->count();

        $distribucion = DB::table('equipos')
            ->leftJoin('clasificaciones_puntos', 'equipos.clasificacion_puntos_id', '=', 'clasificaciones_puntos.id')
            ->groupBy('clasificaciones_puntos.clave', 'clasificaciones_puntos.puntos_base')
            ->selectRaw('clasificaciones_puntos.clave, COUNT(*) as total, clasificaciones_puntos.puntos_base')
            ->orderBy('clasificaciones_puntos.clave')
            ->get();

        $this->command->info("\n".str_repeat('=', 60));
        $this->command->info('RESUMEN DE EQUIPOS POR CLASIFICACIÓN');
        $this->command->info(str_repeat('=', 60));

        foreach ($distribucion as $row) {
            $clave = $row->clave ?? 'SIN ASIGNAR';
            $total = $row->total;
            $puntos = $row->puntos_base ? $row->puntos_base.' pts' : 'N/A';
            $this->command->line(sprintf('  %s: %3d equipos (%s)', $clave, $total, $puntos));
        }

        $this->command->info(str_repeat('=', 60));
        $this->command->info("Total equipos con clasificación: {$totalEquiposConClasificacion}");
        $this->command->info('✓ Actualización completada exitosamente');
    }
}
