<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\DespachoVentas;
use App\Models\DespachoVentasEquipo;
use App\Models\Equipo;
use App\Models\EquipoMovimiento;
use App\Models\LoteModeloRecibido;
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

    // =========================================================
    // FLUJO DE APROBACIÓN
    // =========================================================

    /**
     * Envía el despacho a Ventas para aprobación (BORRADOR → ENVIADO).
     *
     * @throws \Exception
     */
    public function enviar(DespachoVentas $despacho): void
    {
        if (! $despacho->puedeEnviarse()) {
            throw new \Exception(
                'El despacho debe estar en BORRADOR y tener al menos un equipo para enviarse.'
            );
        }

        $despacho->update([
            'estatus'    => DespachoVentas::ENVIADO,
            'enviado_at' => now(),
        ]);
    }

    /**
     * Ventas aprueba el despacho (ENVIADO → APROBADO).
     * Transiciona cada equipo al almacén de Ventas.
     *
     * @throws \Exception
     */
    public function aprobar(DespachoVentas $despacho): void
    {
        if (! $despacho->estaEnviado()) {
            throw new \Exception('Solo se pueden aprobar despachos en estado ENVIADO.');
        }

        DB::transaction(function () use ($despacho) {
            $despacho->update([
                'estatus'                => DespachoVentas::APROBADO,
                'aprobado_por_user_id'   => Auth::id(),
                'aprobado_at'            => now(),
            ]);

            $almacenDestino = Almacen::findOrFail($despacho->almacen_destino_id);

            foreach ($despacho->equipos()->with('equipo')->get() as $despachoEquipo) {
                $equipo = $despachoEquipo->equipo;

                if (! $equipo) {
                    continue;
                }

                $this->transicionarEquipoAVentas($equipo, $almacenDestino, $despacho);
            }
        });
    }

    /**
     * Ventas rechaza el despacho (ENVIADO → RECHAZADO).
     * Los equipos permanecen en Preparación con su estado actual.
     *
     * @throws \Exception
     */
    public function rechazar(DespachoVentas $despacho, string $motivo): void
    {
        if (! $despacho->estaEnviado()) {
            throw new \Exception('Solo se pueden rechazar despachos en estado ENVIADO.');
        }

        $despacho->update([
            'estatus'        => DespachoVentas::RECHAZADO,
            'notas_rechazo'  => trim($motivo),
        ]);
    }

    /**
     * Cancela el despacho (solo desde BORRADOR).
     *
     * @throws \Exception
     */
    public function cancelar(DespachoVentas $despacho): void
    {
        if (! $despacho->esBorrador()) {
            throw new \Exception('Solo se pueden cancelar despachos en BORRADOR.');
        }

        $despacho->update(['estatus' => DespachoVentas::CANCELADO]);
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
