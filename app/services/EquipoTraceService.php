<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\EquipoAuditoria;
use App\Models\EquipoEliminacion;
use App\Models\EquipoMovimiento;
use Illuminate\Support\Collection;
use Throwable;

class EquipoTraceService
{
    private const AREAS_FORENSES = [
        Equipo::AREA_EN_CALIDAD,
        Equipo::AREA_FINALIZADO,
        Equipo::AREA_TRANSFERIDO,
    ];

    public function requiereSnapshotForense(Equipo $equipo): bool
    {
        return in_array($equipo->estatus_area, self::AREAS_FORENSES, true);
    }

    public function registrarAuditoria(
        Equipo $equipo,
        string $accion,
        ?string $motivo = null,
        array $cambios = []
    ): void {
        if (empty($cambios) && $accion === 'EDITADO') {
            return;
        }

        EquipoAuditoria::create([
            'equipo_id'  => $equipo->id,
            'user_id'    => (int) auth()->id(),
            'accion'     => $accion,
            'motivo'     => filled($motivo) ? trim($motivo) : null,
            'cambios'    => $cambios ?: null,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function crearSnapshotEliminacion(Equipo $equipo, string $motivo): EquipoEliminacion
    {
        $equipo->loadMissing(['gpus', 'baterias', 'monitor', 'movimientos', 'estancias']);

        return EquipoEliminacion::create([
            'accion'             => 'ELIMINACION_INTENTO',
            'equipo_id_original' => $equipo->id,
            'numero_serie'       => $equipo->numero_serie ?: ('SIN_SERIE_' . $equipo->id),
            'codigo'             => $equipo->codigo,
            'tipo_equipo'        => $equipo->tipo_equipo,
            'marca'              => $equipo->marca,
            'modelo'             => $equipo->modelo,
            'user_id'            => (int) auth()->id(),
            'motivo'             => trim($motivo),
            'snapshot'           => [
                'equipo'     => $equipo->toArray(),
                'gpus'       => $equipo->gpus->toArray(),
                'baterias'   => $equipo->baterias->toArray(),
                'monitor'    => $equipo->monitor?->toArray(),
                'auditorias' => EquipoAuditoria::where('equipo_id', $equipo->id)->get()->toArray(),
                'movimientos'=> $equipo->movimientos->toArray(),
                'estancias'  => $equipo->estancias->toArray(),
            ],
            'ip'                 => request()->ip(),
            'user_agent'         => substr((string) request()->userAgent(), 0, 250),
        ]);
    }

    public function marcarEliminacionConfirmada(EquipoEliminacion $registro): void
    {
        $registro->update(['accion' => 'ELIMINACION_CONFIRMADA']);
    }

    public function marcarEliminacionFallida(EquipoEliminacion $registro, Throwable $e): void
    {
        $detalle = mb_substr($e->getMessage(), 0, 180);

        $registro->update([
            'accion' => 'ELIMINACION_FALLIDA',
            'motivo' => trim($registro->motivo . ' | Error: ' . $detalle),
        ]);
    }

    /**
     * Timeline unificado de trazabilidad del equipo (más reciente primero).
     */
    public function obtenerTimeline(Equipo|int $equipo): Collection
    {
        $equipoId = $equipo instanceof Equipo ? $equipo->id : (int) $equipo;

        $auditorias = EquipoAuditoria::query()
            ->where('equipo_id', $equipoId)
            ->get()
            ->map(fn (EquipoAuditoria $item) => [
                'tipo_evento' => 'AUDITORIA',
                'origen' => 'equipo_auditorias',
                'id' => $item->id,
                'equipo_id' => $item->equipo_id,
                'accion' => $item->accion,
                'motivo' => $item->motivo,
                'usuario_id' => $item->user_id,
                'fecha' => $item->created_at,
                'payload' => [
                    'cambios' => $item->cambios,
                ],
            ]);

        $movimientos = EquipoMovimiento::query()
            ->where('equipo_id', $equipoId)
            ->get()
            ->map(fn (EquipoMovimiento $item) => [
                'tipo_evento' => 'MOVIMIENTO',
                'origen' => 'equipo_movimientos',
                'id' => $item->id,
                'equipo_id' => $item->equipo_id,
                'accion' => $item->tipo,
                'motivo' => $item->motivo,
                'usuario_id' => $item->created_by,
                'fecha' => $item->created_at,
                'payload' => [
                    'desde_almacen_id' => $item->desde_almacen_id,
                    'hacia_almacen_id' => $item->hacia_almacen_id,
                ],
            ]);

        $eliminaciones = EquipoEliminacion::query()
            ->where('equipo_id_original', $equipoId)
            ->get()
            ->map(fn (EquipoEliminacion $item) => [
                'tipo_evento' => 'ELIMINACION',
                'origen' => 'equipo_eliminaciones',
                'id' => $item->id,
                'equipo_id' => $item->equipo_id_original,
                'accion' => $item->accion,
                'motivo' => $item->motivo,
                'usuario_id' => $item->user_id,
                'fecha' => $item->created_at,
                'payload' => [
                    'numero_serie' => $item->numero_serie,
                    'codigo' => $item->codigo,
                    'tipo_equipo' => $item->tipo_equipo,
                    'marca' => $item->marca,
                    'modelo' => $item->modelo,
                ],
            ]);

        return $auditorias
            ->merge($movimientos)
            ->merge($eliminaciones)
            ->sortByDesc(fn (array $item) => $item['fecha']?->getTimestamp() ?? 0)
            ->values();
    }
}

