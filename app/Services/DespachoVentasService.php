<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\DespachoVentas;
use App\Models\DespachoVentasEquipo;
use App\Models\DespachoVentasProducto;
use App\Models\Equipo;
use App\Models\EquipoMovimiento;
use App\Models\InventarioProducto;
use App\Models\LoteModeloRecibido;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DespachoVentasService
{
    public function __construct(
        private readonly EquipoMovimientoService $movimientoService,
        private readonly EquipoTraceService $traceService,
    ) {}

    // =========================================================
    // CREAR DESPACHO
    // =========================================================

    /**
     * Crea un nuevo despacho en estado BORRADOR.
     *
     * @param  int         $sucursalDestinoId
     * @param  int         $almacenDestinoId
     * @param  string|null $motivo
     * @return DespachoVentas
     */
    public function crear(
        int $sucursalDestinoId,
        int $almacenDestinoId,
        ?string $motivo = null
    ): DespachoVentas {
        return DB::transaction(function () use ($sucursalDestinoId, $almacenDestinoId, $motivo) {
            return DespachoVentas::create([
                'folio'                  => DespachoVentas::generarFolio(),
                'sucursal_destino_id'    => $sucursalDestinoId,
                'almacen_destino_id'     => $almacenDestinoId,
                'creado_por_user_id'     => Auth::id(),
                'estatus'                => DespachoVentas::BORRADOR,
                'motivo'                 => $motivo ? trim($motivo) : null,
            ]);
        });
    }

    // =========================================================
    // GESTIÓN DE EQUIPOS EN EL DESPACHO
    // =========================================================

    /**
     * Agrega un equipo al despacho (debe estar en BORRADOR).
     * Calcula el precio sugerido desde el lote si no se proporciona.
     *
     * @throws \Exception
     */
    public function agregarEquipo(
        DespachoVentas $despacho,
        Equipo $equipo,
        ?float $precioSugerido = null,
        ?string $observacion = null
    ): DespachoVentasEquipo {
        return DB::transaction(function () use ($despacho, $equipo, $precioSugerido, $observacion) {
            // Validaciones de estado
            if (! $despacho->esBorrador()) {
                throw new \Exception('Solo se pueden agregar equipos a despachos en estado BORRADOR.');
            }

            if (! $equipo->tieneNumeroSerie()) {
                throw new \Exception(
                    "El equipo #{$equipo->id} no tiene número de serie capturado y no puede despacharse a Ventas."
                );
            }

            if (! $equipo->puedeDespacharseAVentas()) {
                throw new \Exception(
                    "El equipo [{$equipo->numero_serie}] no está en estado FINALIZADO. ".
                    "Estado actual: {$equipo->estatus_area}"
                );
            }

            // Verificar que no esté ya en un despacho activo
            $despachoActivo = DespachoVentasEquipo::where('equipo_id', $equipo->id)
                ->whereHas('despacho', fn ($q) => $q->whereIn('estatus', [
                    DespachoVentas::BORRADOR,
                    DespachoVentas::ENVIADO,
                ]))
                ->exists();

            if ($despachoActivo) {
                throw new \Exception(
                    "El equipo [{$equipo->numero_serie}] ya está incluido en otro despacho activo."
                );
            }

            // Verificar que no esté ya en este despacho
            $yaEnDespacho = DespachoVentasEquipo::where('despacho_ventas_id', $despacho->id)
                ->where('equipo_id', $equipo->id)
                ->exists();

            if ($yaEnDespacho) {
                throw new \Exception(
                    "El equipo [{$equipo->numero_serie}] ya está en este despacho."
                );
            }

            // Calcular precio sugerido si no se dio
            if ($precioSugerido === null) {
                $precioSugerido = $this->calcularPrecioBase($equipo);
            }

            return DespachoVentasEquipo::create([
                'despacho_ventas_id' => $despacho->id,
                'equipo_id'          => $equipo->id,
                'precio_sugerido'    => $precioSugerido,
                'observacion'        => $observacion ? trim($observacion) : null,
            ]);
        });
    }

    /**
     * Elimina un equipo del despacho (solo en BORRADOR).
     *
     * @throws \Exception
     */
    public function quitarEquipo(DespachoVentas $despacho, Equipo $equipo): void
    {
        if (! $despacho->esBorrador()) {
            throw new \Exception('Solo se pueden quitar equipos de despachos en BORRADOR.');
        }

        DespachoVentasEquipo::where('despacho_ventas_id', $despacho->id)
            ->where('equipo_id', $equipo->id)
            ->delete();
    }

    /**
     * Actualiza el precio sugerido de un equipo en el despacho.
     *
     * @throws \Exception
     */
    public function actualizarPrecio(
        DespachoVentas $despacho,
        int $despachoEquipoId,
        float $nuevoPrecio
    ): void {
        if (! $despacho->esBorrador()) {
            throw new \Exception('Solo se puede editar el precio en despachos en BORRADOR.');
        }

        DespachoVentasEquipo::where('id', $despachoEquipoId)
            ->where('despacho_ventas_id', $despacho->id)
            ->update(['precio_sugerido' => $nuevoPrecio]);
    }

    /**
     * Agrega un producto / accesorio comercial al despacho validando stock real en Preparación.
     */
    public function agregarProducto(
        DespachoVentas $despacho,
        Producto $producto,
        int $cantidad = 1,
        ?float $precioSugerido = null,
        ?string $observacion = null
    ): DespachoVentasProducto {
        if (! $despacho->esBorrador()) {
            throw new \Exception('Solo se pueden agregar productos a despachos en estado BORRADOR.');
        }

        if ($cantidad < 1) {
            throw new \Exception('La cantidad debe ser al menos 1.');
        }

        return DB::transaction(function () use ($despacho, $producto, $cantidad, $precioSugerido, $observacion) {
            $existente = DespachoVentasProducto::where('despacho_ventas_id', $despacho->id)
                ->where('producto_id', $producto->id)
                ->lockForUpdate()
                ->first();

            $cantidadFinal = ($existente?->cantidad ?? 0) + $cantidad;

            $stockOrigen = InventarioProducto::where('producto_id', $producto->id)
                ->where('almacen_id', Almacen::PREPARACION)
                ->lockForUpdate()
                ->value('cantidad') ?? 0;

            if ($stockOrigen < $cantidadFinal) {
                throw new \Exception(
                    "Stock insuficiente en Preparación para '{$producto->nombre}'. Disponible: {$stockOrigen}, solicitado: {$cantidadFinal}."
                );
            }

            if ($existente) {
                $existente->increment('cantidad', $cantidad);

                return $existente->fresh();
            }

            return DespachoVentasProducto::create([
                'despacho_ventas_id' => $despacho->id,
                'producto_id'        => $producto->id,
                'cantidad'           => $cantidad,
                'precio_sugerido'    => $precioSugerido ?: $producto->precio_venta,
                'observacion'        => $observacion ? trim($observacion) : null,
            ]);
        });
    }

    /**
     * Quita un producto / accesorio del despacho.
     */
    public function quitarProducto(DespachoVentas $despacho, int $productoId): void
    {
        if (! $despacho->esBorrador()) {
            throw new \Exception('Solo se pueden quitar productos de despachos en BORRADOR.');
        }

        DespachoVentasProducto::where('despacho_ventas_id', $despacho->id)
            ->where('producto_id', $productoId)
            ->delete();
    }

    // =========================================================
    // FLUJO DE APROBACIÓN
    // =========================================================

    /**
     * Envía el despacho a Ventas para aprobación (BORRADOR → ENVIADO).
     * Verifica equipos listos y descuenta/reserva stock real de productos en Preparación.
     *
     * @throws \Exception
     */
    public function enviar(DespachoVentas $despacho): void
    {
        DB::transaction(function () use ($despacho) {
            $despacho = DespachoVentas::whereKey($despacho->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $despacho->puedeEnviarse()) {
                throw new \Exception(
                    'El despacho debe estar en BORRADOR y tener al menos un equipo o producto para enviarse.'
                );
            }

            // Validar equipos vigentes antes de salir
            foreach ($despacho->equipos()->orderBy('equipo_id')->get() as $despachoEquipo) {
                $equipo = Equipo::whereKey($despachoEquipo->equipo_id)->lockForUpdate()->first();
                if (! $equipo || ! $equipo->puedeDespacharseAVentas()) {
                    $serie = $equipo?->numero_serie ?: "#{$despachoEquipo->equipo_id}";
                    throw new \Exception("El equipo [{$serie}] ya no cumple los requisitos para despacharse a Ventas.");
                }
            }

            // Descontar/reservar productos del almacén de origen (Preparación) al enviar
            foreach ($despacho->productos()->with('producto')->orderBy('producto_id')->get() as $despachoProd) {
                $stockOrigen = InventarioProducto::where('producto_id', $despachoProd->producto_id)
                    ->where('almacen_id', Almacen::PREPARACION)
                    ->lockForUpdate()
                    ->first();

                $disponible = $stockOrigen->cantidad ?? 0;
                if (! $stockOrigen || $disponible < $despachoProd->cantidad) {
                    $nombre = $despachoProd->producto?->nombre ?? "Producto #{$despachoProd->producto_id}";
                    throw new \Exception(
                        "Stock insuficiente en Preparación para '{$nombre}'. Disponible: {$disponible}, requerido: {$despachoProd->cantidad}."
                    );
                }

                $stockOrigen->decrement('cantidad', $despachoProd->cantidad);
            }

            $despacho->update([
                'estatus'    => DespachoVentas::ENVIADO,
                'enviado_at' => now(),
            ]);
        });
    }

    /**
     * Ventas aprueba el despacho (ENVIADO → APROBADO).
     * Transiciona cada equipo al almacén de Ventas y acredita productos recibidos.
     *
     * @throws \Exception
     */
    public function aprobar(DespachoVentas $despacho): void
    {
        DB::transaction(function () use ($despacho) {
            $despacho = DespachoVentas::whereKey($despacho->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $despacho->estaEnviado()) {
                throw new \Exception('Solo se pueden aprobar despachos en estado ENVIADO.');
            }

            $almacenDestino = Almacen::findOrFail($despacho->almacen_destino_id);

            foreach ($despacho->equipos()->orderBy('equipo_id')->get() as $despachoEquipo) {
                $equipo = Equipo::whereKey($despachoEquipo->equipo_id)->lockForUpdate()->first();

                if (! $equipo) {
                    continue;
                }

                if (! $equipo->puedeDespacharseAVentas()) {
                    $serie = $equipo->numero_serie ?: "#{$equipo->id}";
                    throw new \Exception("El equipo [{$serie}] ya no está disponible para ingresar a Ventas.");
                }

                $this->transicionarEquipoAVentas($equipo, $almacenDestino, $despacho);
            }

            // El stock de origen ya fue descontado al enviar; acreditar al almacén destino.
            $this->acreditarProductosEnAlmacen($despacho, (int) $despacho->almacen_destino_id);

            $despacho->update([
                'estatus'              => DespachoVentas::APROBADO,
                'aprobado_por_user_id' => Auth::id(),
                'aprobado_at'          => now(),
            ]);
        });
    }

    /**
     * Ventas rechaza el despacho (ENVIADO → RECHAZADO).
     * Los equipos permanecen en Preparación y los productos retornan al stock de Preparación.
     *
     * @throws \Exception
     */
    public function rechazar(DespachoVentas $despacho, string $motivo): void
    {
        DB::transaction(function () use ($despacho, $motivo) {
            $despacho = DespachoVentas::whereKey($despacho->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $despacho->estaEnviado()) {
                throw new \Exception('Solo se pueden rechazar despachos en estado ENVIADO.');
            }

            // Devolver al almacén origen (Preparación) los productos descontados al enviar
            $this->acreditarProductosEnAlmacen($despacho, Almacen::PREPARACION);

            $despacho->update([
                'estatus'       => DespachoVentas::RECHAZADO,
                'notas_rechazo' => trim($motivo),
            ]);
        });
    }

    /**
     * Acredita las cantidades de productos de un despacho al almacén indicado (con lockForUpdate).
     */
    private function acreditarProductosEnAlmacen(DespachoVentas $despacho, int $almacenId): void
    {
        foreach ($despacho->productos()->orderBy('producto_id')->get() as $despachoProd) {
            $inv = InventarioProducto::where('producto_id', $despachoProd->producto_id)
                ->where('almacen_id', $almacenId)
                ->lockForUpdate()
                ->first();

            if ($inv) {
                $inv->increment('cantidad', $despachoProd->cantidad);
            } else {
                InventarioProducto::create([
                    'producto_id' => $despachoProd->producto_id,
                    'almacen_id'  => $almacenId,
                    'cantidad'    => $despachoProd->cantidad,
                ]);
            }
        }
    }

    /**
     * Cancela el despacho (solo desde BORRADOR).
     *
     * @throws \Exception
     */
    public function cancelar(DespachoVentas $despacho): void
    {
        DB::transaction(function () use ($despacho) {
            $despacho = DespachoVentas::whereKey($despacho->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $despacho->esBorrador()) {
                throw new \Exception('Solo se pueden cancelar despachos en BORRADOR.');
            }

            $despacho->update(['estatus' => DespachoVentas::CANCELADO]);
        });
    }

    // =========================================================
    // PRECIO BASE
    // =========================================================

    /**
     * Calcula el precio base del equipo a partir del valor_unitario del lote.
     * Por ahora es el mismo valor; cuando se implementen márgenes, se aplicarán aquí.
     */
    public function calcularPrecioBase(Equipo $equipo): ?float
    {
        if (! $equipo->lote_modelo_id) {
            return null;
        }

        $valorUnitario = LoteModeloRecibido::where('id', $equipo->lote_modelo_id)
            ->value('valor_unitario');

        return $valorUnitario ? (float) $valorUnitario : null;
    }

    // =========================================================
    // PRIVADO — TRANSICIÓN DE EQUIPO
    // =========================================================

    /**
     * Transiciona un equipo individual de Preparación a Ventas:
     * - Cambia estatus_ciclo → VENTAS
     * - Cambia estatus_area  → DISPONIBLE_VENTA
     * - Mueve al almacén destino (cierra estancia Prep, abre estancia Ventas)
     * - Registra movimiento DESPACHO_VENTAS
     * - Registra auditoría
     */
    private function transicionarEquipoAVentas(
        Equipo $equipo,
        Almacen $almacenDestino,
        DespachoVentas $despacho
    ): void {
        // Actualizar estados del equipo
        $equipo->update([
            'estatus_ciclo'  => Equipo::CICLO_VENTAS,
            'estatus_area'   => Equipo::AREA_DISPONIBLE_VENTA,
            'sucursal_id'    => $despacho->sucursal_destino_id,
        ]);

        // Mover entre almacenes (cierra estancia Prep, abre en Ventas)
        $this->movimientoService->mover(
            $equipo,
            $almacenDestino,
            'DESPACHO_VENTAS',
            "Despacho a Ventas aprobado. Folio: {$despacho->folio}"
        );

        // Registrar auditoría
        $this->traceService->registrarAuditoria(
            $equipo,
            'DESPACHO_VENTAS_APROBADO',
            "Equipo despachado a Ventas. Folio: {$despacho->folio}. Almacén destino: {$almacenDestino->nombre}",
            [
                'despacho_id'        => $despacho->id,
                'folio'              => $despacho->folio,
                'almacen_destino_id' => $almacenDestino->id,
                'almacen_destino'    => $almacenDestino->nombre,
                'aprobado_por'       => Auth::id(),
            ]
        );
    }
}
