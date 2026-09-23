<?php

use App\Models\Equipo;
use App\Models\GarantiaProveedor;
use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use App\Models\User;

// Buscar al primer usuario que sea administrador, gerente o líder para usarlo de "fantasma"
// Si no hay, usar el ID 1 por defecto
$usuarioAdmin = User::whereHas('role', function($q) {
    $q->whereIn('slug', ['admin', 'gerente', 'lider']);
})->first();

$usuarioId = $usuarioAdmin ? $usuarioAdmin->id : 1;

$equipos = Equipo::where('estatus_area', 'PENDIENTE_GARANTIA')->get();
$countMigradosNormal = 0;
$countMigradosFantasma = 0;

foreach ($equipos as $equipo) {
    $hasWarranty = GarantiaProveedor::where('equipo_id', $equipo->id)
        ->where('estatus', GarantiaProveedor::PENDIENTE)
        ->exists();
        
    if ($hasWarranty) continue;

    $ae = AsignacionEquipo::with('asignacion')
            ->where('equipo_id', $equipo->id)
            ->latest('id')
            ->first();

    if (!$ae) {
        // Creamos una Asignacion "fantasma" para cumplir la integridad referencial en Producción
        $asignacion = Asignacion::create([
            'tecnico_id' => $usuarioId,
            'asignado_por_id' => $usuarioId,
            'lote_modelo_id' => $equipo->lote_modelo_id ?? 1,
            'cantidad' => 1,
            'fecha_asignacion' => now()->toDateString(),
            'estatus' => Asignacion::ENTREGADO ?? 'ENTREGADO',
            'notas' => 'Asignación generada automáticamente para migración de garantías antiguas en Producción.',
        ]);

        $ae = AsignacionEquipo::create([
            'asignacion_id' => $asignacion->id,
            'equipo_id' => $equipo->id,
            'inicio_en' => now(),
            'fin_en' => now(),
            'camino' => 'GARANTIA_EXTERNA', 
            'pre_asignado' => 0,
            'notas' => 'Generado por migración de garantías.'
        ]);
        
        $countMigradosFantasma++;
    } else {
        $countMigradosNormal++;
    }

    $proveedorId = $equipo->loteModelo?->lote?->proveedor_id ?? $equipo->proveedor_id ?? 1;
    $tecnicoId = $ae->asignacion?->tecnico_id ?? $usuarioId;

    GarantiaProveedor::create([
        'equipo_id' => $equipo->id,
        'proveedor_id' => $proveedorId,
        'asignacion_equipo_id' => $ae->id,
        'reportado_por_id' => $tecnicoId,
        'descripcion_defecto' => 'Equipo reportado en el pasado (Migrado automáticamente a nueva vista).',
        'fecha_envio' => null, // Por enviar
        'estatus' => GarantiaProveedor::PENDIENTE,
        'created_at' => $ae->created_at ?? $equipo->updated_at,
        'updated_at' => now(),
    ]);
}

echo "Migración en Producción Completada:\n";
echo "- Equipos normales migrados: {$countMigradosNormal}\n";
echo "- Equipos huérfanos migrados con asignación fantasma: {$countMigradosFantasma}\n";
echo "- TOTAL: " . ($countMigradosNormal + $countMigradosFantasma) . "\n";
