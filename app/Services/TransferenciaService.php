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
        if (! $this->esEncargadoActivo($transferencia->almacen_origen_id)) {
            throw new Exception('No tienes permisos suficientes en el almacén de origen.');
        }

        DB::transaction(function () use ($transferencia) {
            $transferencia = Transferencia::whereKey($transferencia->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transferencia->estatus !== 'BORRADOR') {
                throw new Exception('La transferencia no está en estado BORRADOR.');
            }

            $detalles = $transferencia->detalles()
                ->with('movible')
                ->orderBy('movible_type')
                ->orderBy('movible_id')
                ->get();

            if ($detalles->count() === 0) {
                throw new Exception('No puedes enviar una transferencia sin artículos.');
            }

            // Validar y bloquear equipos en tránsito; descontar stock en origen para consumibles/productos
            foreach ($detalles as $detalle) {
                if ($detalle->esEquipo()) {
                    $equipo = Equipo::whereKey($detalle->movible_id)->lockForUpdate()->first();
                    if (! $equipo) {
                        throw new Exception("El equipo #{$detalle->movible_id} ya no existe.");
                    }
                    if ((int) $equipo->almacen_id !== (int) $transferencia->almacen_origen_id) {
                        throw new Exception("El equipo [{$equipo->numero_serie}] ya no se encuentra en el almacén origen.");
                    }

                    $motivo = '';
                    if (! $equipo->puedeTransferirse($motivo, $transferencia->id)) {
                        throw new Exception("El equipo [{$equipo->numero_serie}] no puede transferirse: {$motivo}");
                    }

                    // Bloqueo operativo mientras viaja
                    $equipo->update([
                        'estatus_area' => Equipo::AREA_TRANSFERIDO,
                    ]);
                } elseif ($detalle->esConsumible() || $detalle->esProducto()) {
                    $this->descontarStock(
                        $detalle->movible_type,
                        (int) $detalle->movible_id,
                        (int) $transferencia->almacen_origen_id,
                        (int) $detalle->cantidad
                    );
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
        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No tienes permisos suficientes en el almacén de destino.');
        }

        DB::transaction(function () use ($transferencia) {
            $transferencia = Transferencia::whereKey($transferencia->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transferencia->estatus !== 'ENVIADA') {
                throw new Exception('La transferencia no está en estado ENVIADA.');
            }

            $destino = $transferencia->destino ?: Almacen::findOrFail($transferencia->almacen_destino_id);
            $movService = $this->getMovimientoService();

            $transferencia->detalles()
                ->with('movible')
                ->orderBy('movible_type')
                ->orderBy('movible_id')
                ->get()
                ->each(function ($detalle) use ($transferencia, $destino, $movService) {

                    if ($detalle->esEquipo() && $detalle->movible) {
                        $equipo = Equipo::whereKey($detalle->movible_id)->lockForUpdate()->first();
                        if (! $equipo) {
                            return;
                        }

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

                    } elseif ($detalle->esConsumible() || $detalle->esProducto()) {
                        // El origen ya fue descontado al enviar; aquí solo acreditamos al destino.
                        $this->acreditarStock(
                            $detalle->movible_type,
                            (int) $detalle->movible_id,
                            (int) $transferencia->almacen_destino_id,
                            (int) $detalle->cantidad
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
        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No tienes permisos suficientes en el almacén de destino.');
        }

        DB::transaction(function () use ($transferencia, $motivo) {
            $transferencia = Transferencia::whereKey($transferencia->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transferencia->estatus !== 'ENVIADA') {
                throw new Exception('Solo se pueden rechazar transferencias ENVIADAS.');
            }

            $this->liberarRecursosDeEnvio($transferencia);

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
        DB::transaction(function () use ($transferencia, $motivo) {
            $transferencia = Transferencia::whereKey($transferencia->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($transferencia->estatus, ['BORRADOR', 'ENVIADA'], true)) {
                throw new Exception('Solo se pueden cancelar transferencias en BORRADOR o ENVIADAS.');
            }

            // Si ya estaba ENVIADA, revertir equipos y devolver stock reservado al origen
            if ($transferencia->estatus === 'ENVIADA') {
                $this->liberarRecursosDeEnvio($transferencia);
            }

            $transferencia->update([
                'estatus'       => 'CANCELADA',
                'observaciones' => $motivo ? trim($motivo) : $transferencia->observaciones,
            ]);
        });
    }

    // ─── Helpers privados de stock e inventario ──────────────────────────────

    /**
     * Revierte equipos y devuelve al almacén origen el stock descontado al enviar.
     */
    private function liberarRecursosDeEnvio(Transferencia $transferencia): void
    {
        $origen = $transferencia->origen ?: Almacen::find($transferencia->almacen_origen_id);

        $transferencia->detalles()
            ->with('movible')
            ->orderBy('movible_type')
            ->orderBy('movible_id')
            ->get()
            ->each(function ($detalle) use ($transferencia, $origen) {
                if ($detalle->esEquipo() && $detalle->movible && $origen) {
                    $equipo = Equipo::whereKey($detalle->movible_id)->lockForUpdate()->first();
                    if ($equipo) {
                        [$cicloOrigen, $areaOrigen] = $this->resolverEstatusDestino($origen, $equipo);
                        $equipo->update([
                            'estatus_ciclo' => $cicloOrigen,
                            'estatus_area'  => $areaOrigen,
                        ]);
                    }
                } elseif ($detalle->esConsumible() || $detalle->esProducto()) {
                    $this->acreditarStock(
                        $detalle->movible_type,
                        (int) $detalle->movible_id,
                        (int) $transferencia->almacen_origen_id,
                        (int) $detalle->cantidad
                    );
                }
            });
    }

    /**
     * Resuelve clase de inventario, columna FK y etiqueta legible según tipo polimórfico.
     *
     * @return array{0: class-string, 1: string, 2: string}
     */
    private function resolverModeloInventario(string $movibleType): array
    {
        return match ($movibleType) {
            Consumible::class => [InventarioConsumible::class, 'consumible_id', 'consumible'],
            Producto::class   => [InventarioProducto::class,   'producto_id',   'producto'],
            default           => throw new Exception("Tipo de inventario no soportado: {$movibleType}"),
        };
    }

    private function descontarStock(
        string $movibleType,
        int $movibleId,
        int $almacenId,
        int $cantidad
    ): void {
        [$modeloInv, $fkCol, $etiqueta] = $this->resolverModeloInventario($movibleType);

        $origen = $modeloInv::where($fkCol, $movibleId)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->first();

        if (! $origen || $origen->cantidad < $cantidad) {
            $disponible = $origen->cantidad ?? 0;
            throw new Exception(
                "Stock insuficiente para el {$etiqueta} #{$movibleId}. Disponible: {$disponible}"
            );
        }

        $origen->decrement('cantidad', $cantidad);
    }

    private function acreditarStock(
        string $movibleType,
        int $movibleId,
        int $almacenId,
        int $cantidad
    ): void {
        [$modeloInv, $fkCol] = $this->resolverModeloInventario($movibleType);

        $registro = $modeloInv::where($fkCol, $movibleId)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->first();

        if ($registro) {
            $registro->increment('cantidad', $cantidad);
        } else {
            $modeloInv::create([
                $fkCol       => $movibleId,
                'almacen_id' => $almacenId,
                'cantidad'   => $cantidad,
            ]);
        }
    }
}
