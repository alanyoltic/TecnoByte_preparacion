<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Venta;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaService
{
    /**
     * Completa una venta: mueve stock real y registra auditorías.
     *
     * Debe llamarse cuando la Venta está en estatus PENDIENTE.
     * Usa DB::transaction + lockForUpdate para ser seguro ante concurrencia.
     */
    public function completar(Venta $venta): void
    {
        if ($venta->estatus !== Venta::ESTATUS_PENDIENTE) {
            throw new Exception('Solo se pueden completar ventas en estatus PENDIENTE.');
        }

        DB::transaction(function () use ($venta) {
            $ventaBloqueada = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();

            if ($ventaBloqueada->estatus !== Venta::ESTATUS_PENDIENTE) {
                throw new Exception('Esta venta ya fue procesada o cancelada previamente.');
            }

            $detalles = $ventaBloqueada->detalles()->with('vendible')->get();

            // Orden determinístico para evitar deadlocks en cobros concurrentes
            $detallesEquipos = $detalles->filter(fn ($d) => $d->esEquipo())->sortBy('vendible_id');
            foreach ($detallesEquipos as $detalle) {
                $this->cerrarEquipoPorId((int) $detalle->vendible_id, $ventaBloqueada);
            }

            $detallesProductos = $detalles->filter(fn ($d) => $d->esProducto())->sortBy('vendible_id');
            foreach ($detallesProductos as $detalle) {
                if (! $detalle->vendible) {
                    throw new Exception("El producto #{$detalle->vendible_id} ya no existe.");
                }
                $detalle->vendible->decrementarStock(
                    $ventaBloqueada->almacen_id,
                    $detalle->cantidad
                );
            }

            $ventaBloqueada->recalcularTotales();

            $ventaBloqueada->update(['estatus' => Venta::ESTATUS_COMPLETADA]);
            $venta->refresh();
        });
    }

    /**
     * Cancela una venta PENDIENTE (antes de ser cobrada).
     * No revierte equipos ni stock — solo cierra el carrito.
     */
    public function cancelarPendiente(Venta $venta, ?string $motivo = null): void
    {
        if ($venta->estatus !== Venta::ESTATUS_PENDIENTE) {
            throw new Exception('Solo se pueden cancelar ventas PENDIENTES desde el POS.');
        }

        $venta->update([
            'estatus'          => Venta::ESTATUS_CANCELADA,
            'notas'            => $motivo ? "[CANCELADA] {$motivo}" : '[CANCELADA]',
            'cancelada_por_id' => Auth::id(),
            'cancelada_en'     => now(),
        ]);
    }

    /**
     * Cancela una venta ya COMPLETADA (post-cobro).
     * Revierte el stock de productos y libera los equipos si aún se puede.
     *
     * ⚠ Solo debe permitirse en un plazo razonable (p. ej. mismo día) y
     *   con permiso especial: ventas.ventas.cancelar
     */
    public function cancelarCompletada(Venta $venta, ?string $motivo = null): void
    {
        if ($venta->estatus !== Venta::ESTATUS_COMPLETADA) {
            throw new Exception('Solo se pueden cancelar ventas en estatus COMPLETADA.');
        }

        DB::transaction(function () use ($venta, $motivo) {

            $venta->detalles()->with('vendible')->get()
                ->each(function ($detalle) use ($venta) {

                    if ($detalle->esEquipo()) {
                        // Revertir equipo a DISPONIBLE_VENTA si aún no fue recogido
                        $equipo = $detalle->vendible;
                        if ($equipo && $equipo->estatus_ciclo === Equipo::CICLO_VENDIDO) {
                            $equipo->update([
                                'estatus_ciclo' => Equipo::CICLO_VENTAS,
                                'estatus_area'  => Equipo::AREA_DISPONIBLE_VENTA,
                                'almacen_id'    => $venta->almacen_id,
                            ]);

                            app(EquipoTraceService::class)->registrarAuditoria(
                                $equipo,
                                'VENTA_CANCELADA',
                                $motivo ?? 'Venta cancelada por administrador',
                                ['venta_id' => $venta->id]
                            );

                            // Revertir estatus de cargador a DISPONIBLE
                            $cargador = \App\Models\Cargador::where('equipo_id', $equipo->id)->first();
                            if ($cargador) {
                                $cargador->update([
                                    'estatus' => 'DISPONIBLE'
                                ]);
                                \App\Services\CargadorTraceService::log($cargador, 'DESASIGNADO', 'Venta cancelada, cargador vuelve a estar disponible.');
                            }
                        }

                    } elseif ($detalle->esProducto()) {
                        // Devolver stock al almacén de origen
                        $detalle->vendible->incrementarStock(
                            $venta->almacen_id,
                            $detalle->cantidad
                        );
                    }
                });

            $venta->update([
                'estatus'          => Venta::ESTATUS_CANCELADA,
                'notas'            => "[CANCELADA] {$motivo}",
                'cancelada_por_id' => Auth::id(),
                'cancelada_en'     => now(),
            ]);
        });
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Bloquea con lockForUpdate, revalida disponibilidad y marca un equipo como VENDIDO.
     */
    private function cerrarEquipoPorId(int $equipoId, Venta $venta): void
    {
        $equipo = Equipo::whereKey($equipoId)->lockForUpdate()->first();

        if (! $equipo) {
            throw new Exception("El equipo #{$equipoId} no existe en el sistema.");
        }

        $this->cerrarEquipo($equipo, $venta);
    }

    /**
     * Marca un equipo como VENDIDO y registra la auditoría correspondiente.
     */
    private function cerrarEquipo(Equipo $equipo, Venta $venta): void
    {
        if (! $equipo->tieneNumeroSerie()) {
            throw new Exception("El equipo #{$equipo->id} no tiene número de serie capturado y no puede venderse.");
        }

        if ($equipo->estatus_ciclo !== Equipo::CICLO_VENTAS || $equipo->estatus_area !== Equipo::AREA_DISPONIBLE_VENTA) {
            throw new Exception(
                "El equipo [{$equipo->numero_serie}] ya fue vendido o no está disponible para venta (estado actual: {$equipo->estatus_ciclo} / {$equipo->estatus_area})."
            );
        }

        if ($venta->sucursal_id && $equipo->sucursal_id && (int) $equipo->sucursal_id !== (int) $venta->sucursal_id) {
            throw new Exception(
                "El equipo [{$equipo->numero_serie}] pertenece a otra sucursal y no puede venderse desde esta caja."
            );
        }

        $almacenOrigenId = $equipo->almacen_id ?: $venta->almacen_id;

        $equipo->update([
            'estatus_ciclo' => Equipo::CICLO_VENDIDO,
            // EN_PISO_VENTA es el último área antes de VENDIDO en el ciclo.
            // Una vez que el ciclo es VENDIDO, el área ya no se usa operativamente.
            'estatus_area'  => Equipo::AREA_EN_PISO_VENTA,
        ]);

        // Registrar en equipo_movimientos (tipo VENTA ya existe en el ENUM)
        app(EquipoMovimientoService::class)->registrar(
            equipo    : $equipo,
            tipo      : 'VENTA',
            desde     : $almacenOrigenId,
            hacia     : null,
            motivo    : "Venta #{$venta->folio}",
            createdBy : $venta->vendedor_id
        );

        // Registrar en equipo_auditorias para la línea de tiempo
        app(EquipoTraceService::class)->registrarAuditoria(
            equipo   : $equipo,
            accion   : 'VENDIDO',
            motivo   : "Venta {$venta->folio} — cliente #{$venta->cliente_id}",
            cambios  : [
                'venta_id'      => $venta->id,
                'estatus_ciclo' => Equipo::CICLO_VENDIDO,
            ]
        );
    }
}
