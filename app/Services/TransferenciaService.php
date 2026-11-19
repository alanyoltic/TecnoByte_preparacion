<?php

namespace App\Services;

use App\Models\Almacen;
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

    public function __construct(
        private readonly ?EquipoMovimientoService $movimientoService = null
    ) {
        $this->user = Auth::user();
    }

    private function getMovimientoService(): EquipoMovimientoService
    {
        return $this->movimientoService ?: app(EquipoMovimientoService::class);
    }

    // ─── Guards & Permisos ───────────────────────────────────────────────────

    public function esEncargadoActivo(int $almacenId): bool
    {
        $user = $this->user ?: Auth::user();
        if (! $user) {
            return false;
        }

        // Roles directivos y administrativos tienen permiso automático
        if (method_exists($user, 'esCeoOAdmin') && $user->esCeoOAdmin()) {
            return true;
        }
        if (in_array($user->role?->slug, ['ceo', 'admin', 'admin_sistema'], true)) {
            return true;
        }

        $almacen = Almacen::find($almacenId);
        if (! $almacen) {
            return false;
        }

        // Si es gerente del departamento al que pertenece el almacén
        if ($user->role?->slug === 'gerente' && $user->departamento_id == $almacen->departamento_id) {
            return true;
        }

        // Si el usuario pertenece a la misma sucursal del almacén
        if ($user->sucursal_id && $user->sucursal_id == $almacen->sucursal_id) {
            return true;
        }

        // Validación en tabla de encargados activos
        return AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('user_id', $user->id)
            ->where('activo', 1)
            ->where(function ($q) {
                $q->whereNull('hasta')
                  ->orWhere('hasta', '>=', Carbon::now());
            })
            ->where('desde', '<=', Carbon::now())
            ->exists();
    }

    // ─── Matriz de Resolución Automática de Estatus ──────────────────────────

    /**
     * Deduce el estatus_ciclo y estatus_area que debe recibir un equipo al llegar al almacén destino.
     * Retorna array: [estatus_ciclo, estatus_area].
     */
    public function resolverEstatusDestino(Almacen $destino, Equipo $equipo): array
    {
        // 1. Destino pertenece al área de VENTAS (departamento_id = 2)
        if ($destino->departamento_id == 2) {
            $claveLower = strtolower($destino->clave ?? '');
            $nombreLower = strtolower($destino->nombre ?? '');
            if ($destino->clave === Almacen::CLAVE_APARTADOS || str_contains($claveLower, 'apar') || str_contains($nombreLower, 'apartad')) {
                return [Equipo::CICLO_APARTADO, Equipo::AREA_APARTADO_CLIENTE];
            }
            return [Equipo::CICLO_VENTAS, Equipo::AREA_DISPONIBLE_VENTA];
        }

        // 2. Destino pertenece al área de PREPARACIÓN / TALLER / CEDIS (departamento_id = 1)
        if ($destino->departamento_id == 1) {
            return match ((int) $destino->id) {
                Almacen::PREPARACION        => [Equipo::CICLO_PREPARACION, Equipo::AREA_SIN_ASIGNAR],
                Almacen::GARANTIAS_INTERNAS => [Equipo::CICLO_PREPARACION, Equipo::AREA_GARANTIA_INT],
                Almacen::GARANTIAS_EXTERNAS => [Equipo::CICLO_PREPARACION, Equipo::AREA_GARANTIA_EXT],
                Almacen::CALIDAD            => [Equipo::CICLO_CALIDAD, Equipo::AREA_EN_CALIDAD],
                Almacen::AREA_TRANSFERENCIA => [Equipo::CICLO_CALIDAD, Equipo::AREA_FINALIZADO],
                Almacen::SCRAP              => [Equipo::CICLO_SCRAP, Equipo::AREA_PENDIENTE_DESARME],
                Almacen::PIEZAS_PENDIENTES  => [Equipo::CICLO_PREPARACION, Equipo::AREA_PENDIENTE_PIEZA],
                Almacen::CEDIS              => [Equipo::CICLO_CEDIS, Equipo::AREA_SIN_ASIGNAR],
                default                     => [Equipo::CICLO_PREPARACION, Equipo::AREA_SIN_ASIGNAR],
            };
        }

        // Fallback seguro por defecto
        return [Equipo::CICLO_PREPARACION, Equipo::AREA_SIN_ASIGNAR];
    }

    // ─── Acciones de estado ─────────────────────────────────────────────────

    public function enviar(Transferencia $transferencia): void
    {
        if ($transferencia->estatus !== 'BORRADOR') {
            throw new Exception('La transferencia no está en estado BORRADOR.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_origen_id)) {
            throw new Exception('No tienes permisos suficientes en el almacén de origen.');
        }

        $detalles = $transferencia->detalles()->with('movible')->get();
        if ($detalles->count() === 0) {
            throw new Exception('No puedes enviar una transferencia sin artículos.');
        }

        DB::transaction(function () use ($transferencia, $detalles) {
            // Validar y bloquear en tránsito cada equipo serializado
            foreach ($detalles as $detalle) {
                if ($detalle->esEquipo() && $detalle->movible) {
                    $equipo = $detalle->movible;
                    $motivo = '';
                    if (! $equipo->puedeTransferirse($motivo, $transferencia->id)) {
                        throw new Exception("El equipo [{$equipo->numero_serie}] no puede transferirse: {$motivo}");
                    }

                    // Bloqueo operativo mientras viaja
                    $equipo->update([
                        'estatus_area' => Equipo::AREA_TRANSFERIDO,
                    ]);
                }
            }

            $transferencia->update([
                'estatus'    => 'ENVIADA',
                'enviada_at' => now(),
            ]);
        });
    }

    /**
     * Acepta la transferencia y ejecuta los movimientos reales de stock y estancias físicas.
     */
    public function aceptar(Transferencia $transferencia): void
    {
        if ($transferencia->estatus !== 'ENVIADA') {
            throw new Exception('La transferencia no está en estado ENVIADA.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No tienes permisos suficientes en el almacén de destino.');
        }

        DB::transaction(function () use ($transferencia) {
            $destino = $transferencia->destino ?: Almacen::findOrFail($transferencia->almacen_destino_id);
            $movService = $this->getMovimientoService();

            $transferencia->detalles()->with('movible')->get()
                ->each(function ($detalle) use ($transferencia, $destino, $movService) {

                    if ($detalle->esEquipo() && $detalle->movible) {
                        $equipo = $detalle->movible;

                        // Determinar automáticamente el estatus correspondiente al nuevo almacén
                        [$nuevoCiclo, $nuevoArea] = $this->resolverEstatusDestino($destino, $equipo);

                        $equipo->update([
                            'estatus_ciclo' => $nuevoCiclo,
                            'estatus_area'  => $nuevoArea,
                            'sucursal_id'   => $destino->sucursal_id ?: $equipo->sucursal_id,
                        ]);

                        // Sincronizar estancia física (cierra la vieja y abre la nueva)
                        $movService->mover(
                            $equipo,
                            $destino,
                            'MOVER_ALMACEN',
                            "Transferencia #{$transferencia->id} recibida en {$destino->nombre}."
                        );

                    } elseif ($detalle->esConsumible()) {
                        $this->moverStockConsumible(
                            $detalle->movible_id,
                            $transferencia->almacen_origen_id,
                            $transferencia->almacen_destino_id,
                            $detalle->cantidad
                        );

                    } elseif ($detalle->esProducto()) {
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
                'approved_by' => $this->user?->id ?: Auth::id(),
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
            throw new Exception('No tienes permisos suficientes en el almacén de destino.');
        }

        DB::transaction(function () use ($transferencia, $motivo) {
            $origen = $transferencia->origen ?: Almacen::find($transferencia->almacen_origen_id);

            // Revertir el bloqueo de los equipos y devolverlos a su estado en el origen
            $transferencia->detalles()->with('movible')->get()
                ->each(function ($detalle) use ($origen) {
                    if ($detalle->esEquipo() && $detalle->movible && $origen) {
                        $equipo = $detalle->movible;
                        [$cicloOrigen, $areaOrigen] = $this->resolverEstatusDestino($origen, $equipo);
                        $equipo->update([
                            'estatus_ciclo' => $cicloOrigen,
                            'estatus_area'  => $areaOrigen,
                        ]);
                    }
                });

            $transferencia->update([
                'estatus'       => 'RECHAZADA',
                'approved_by'   => $this->user?->id ?: Auth::id(),
                'aprobada_at'   => now(),
                'observaciones' => $motivo ? trim($motivo) : $transferencia->observaciones,
            ]);
        });
    }

    public function cancelar(Transferencia $transferencia, ?string $motivo = null): void
    {
        if (! in_array($transferencia->estatus, ['BORRADOR', 'ENVIADA'], true)) {
            throw new Exception('Solo se pueden cancelar transferencias en BORRADOR o ENVIADAS.');
        }

        DB::transaction(function () use ($transferencia, $motivo) {
            // Si ya estaba ENVIADA, liberar los equipos de regreso al estado de origen
            if ($transferencia->estatus === 'ENVIADA') {
                $origen = $transferencia->origen ?: Almacen::find($transferencia->almacen_origen_id);
                $transferencia->detalles()->with('movible')->get()
                    ->each(function ($detalle) use ($origen) {
                        if ($detalle->esEquipo() && $detalle->movible && $origen) {
                            $equipo = $detalle->movible;
                            [$cicloOrigen, $areaOrigen] = $this->resolverEstatusDestino($origen, $equipo);
                            $equipo->update([
                                'estatus_ciclo' => $cicloOrigen,
                                'estatus_area'  => $areaOrigen,
                            ]);
                        }
                    });
            }

            $transferencia->update([
                'estatus'       => 'CANCELADA',
                'observaciones' => $motivo ? trim($motivo) : $transferencia->observaciones,
            ]);
        });
    }

    // ─── Movimientos de stock internos (Consumibles y Productos) ─────────────

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
