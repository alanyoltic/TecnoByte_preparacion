<?php

namespace App\Services;

use App\Models\AlmacenEncargado;
use App\Models\Consumible;
use App\Models\Equipo;
use App\Models\InventarioConsumible;
use App\Models\InventarioProducto;
use App\Models\Producto;
use App\Models\Transferencia;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferenciaService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    // ─── Guards ─────────────────────────────────────────────────────────────

    protected function esEncargadoActivo(int $almacenId): bool
    {
        return AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('user_id', $this->user->id)
            ->where('activo', 1)
            ->where(function ($q) {
                $q->whereNull('hasta')
                  ->orWhere('hasta', '>=', Carbon::now());
            })
            ->where('desde', '<=', Carbon::now())
            ->exists();
    }

    // ─── Acciones de estado ─────────────────────────────────────────────────

    public function enviar(Transferencia $transferencia): void
    {
        if ($transferencia->estatus !== 'BORRADOR') {
            throw new Exception('La transferencia no está en estado BORRADOR.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_origen_id)) {
            throw new Exception('No eres encargado del almacén origen.');
        }

        if ($transferencia->detalles()->count() === 0) {
            throw new Exception('No puedes enviar una transferencia sin artículos.');
        }

        $transferencia->update([
            'estatus'    => 'ENVIADA',
            'enviada_at' => now(),
        ]);
    }

    /**
     * Acepta la transferencia y ejecuta los movimientos reales de stock.
     *
     * Para cada detalle:
     *   - Equipo      → actualiza almacen_id al destino
     *   - Consumible  → mueve cantidad en inventario_consumibles
     *   - Producto    → mueve cantidad en inventario_productos
     */
    public function aceptar(Transferencia $transferencia): void
    {
        if ($transferencia->estatus !== 'ENVIADA') {
            throw new Exception('La transferencia no está en estado ENVIADA.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No eres encargado del almacén destino.');
        }

        DB::transaction(function () use ($transferencia) {

            $transferencia->detalles()->with('movible')->get()
                ->each(function ($detalle) use ($transferencia) {

                    if ($detalle->esEquipo()) {
                        // ── Mover equipo serializado ────────────────────────
                        $detalle->movible->update([
                            'almacen_id' => $transferencia->almacen_destino_id,
                        ]);

                    } elseif ($detalle->esConsumible()) {
                        // ── Mover consumible interno ────────────────────────
                        $this->moverStockConsumible(
                            $detalle->movible_id,
                            $transferencia->almacen_origen_id,
                            $transferencia->almacen_destino_id,
                            $detalle->cantidad
                        );

                    } elseif ($detalle->esProducto()) {
                        // ── Mover producto comercial ────────────────────────
                        $this->moverStockProducto(
                            $detalle->movible_id,
                            $transferencia->almacen_origen_id,
                            $transferencia->almacen_destino_id,
                            $detalle->cantidad
                        );
                    }
                });

            $transferencia->update([
                'estatus'     => 'ACEPTADA',
                'approved_by' => $this->user->id,
                'aprobada_at' => now(),
            ]);
        });
    }

    public function rechazar(Transferencia $transferencia, ?string $motivo = null): void
    {
        if ($transferencia->estatus !== 'ENVIADA') {
            throw new Exception('Solo se pueden rechazar transferencias ENVIADAS.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No eres encargado del almacén destino.');
        }

        $transferencia->update([
            'estatus'     => 'RECHAZADA',
            'approved_by' => $this->user->id,
            'aprobada_at' => now(),
            'observaciones' => $motivo,
        ]);
    }

    // ─── Movimientos de stock internos ──────────────────────────────────────

    /**
     * Mueve N unidades de un consumible de un almacén a otro.
     */
    private function moverStockConsumible(
        int $consumibleId,
        int $origenId,
        int $destinoId,
        int $cantidad
    ): void {
        $origen = InventarioConsumible::where('consumible_id', $consumibleId)
            ->where('almacen_id', $origenId)
            ->lockForUpdate()
            ->first();

        if (! $origen || $origen->cantidad < $cantidad) {
            $disponible = $origen->cantidad ?? 0;
            throw new Exception(
                "Stock insuficiente para el consumible #{$consumibleId}. Disponible: {$disponible}"
            );
        }

        $origen->decrement('cantidad', $cantidad);

        InventarioConsumible::firstOrCreate(
            ['consumible_id' => $consumibleId, 'almacen_id' => $destinoId],
            ['cantidad' => 0]
        )->increment('cantidad', $cantidad);
    }

    /**
     * Mueve N unidades de un producto de un almacén a otro.
     */
    private function moverStockProducto(
        int $productoId,
        int $origenId,
        int $destinoId,
        int $cantidad
    ): void {
        $origen = InventarioProducto::where('producto_id', $productoId)
            ->where('almacen_id', $origenId)
            ->lockForUpdate()
            ->first();

        if (! $origen || $origen->cantidad < $cantidad) {
            $disponible = $origen->cantidad ?? 0;
            throw new Exception(
                "Stock insuficiente para el producto #{$productoId}. Disponible: {$disponible}"
            );
        }

        $origen->decrement('cantidad', $cantidad);

        InventarioProducto::firstOrCreate(
            ['producto_id' => $productoId, 'almacen_id' => $destinoId],
            ['cantidad' => 0]
        )->increment('cantidad', $cantidad);
    }
}
