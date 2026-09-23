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

            $venta->detalles()->with('vendible')->get()
                ->each(function ($detalle) use ($venta) {

                    if ($detalle->esEquipo()) {
                        $this->cerrarEquipo($detalle->vendible, $venta);

                    } elseif ($detalle->esProducto()) {
                        $detalle->vendible->decrementarStock(
                            $venta->almacen_id,
                            $detalle->cantidad
                        );
                    }
                });

            $venta->recalcularTotales();

            $venta->update(['estatus' => Venta::ESTATUS_COMPLETADA]);
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
     * Marca un equipo como VENDIDO y registra la auditoría correspondiente.
     */
    private function cerrarEquipo(Equipo $equipo, Venta $venta): void
    {
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
            desde     : $venta->almacen_id,
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
