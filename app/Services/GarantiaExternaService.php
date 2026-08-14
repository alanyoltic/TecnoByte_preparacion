<?php

namespace App\Services;

use App\Models\AsignacionEquipo;
use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\GarantiaProveedor;
use App\Models\LoteModeloRecibido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GarantiaExternaService
 *
 * Encapsula la lógica de negocio para gestionar garantías externas de proveedor.
 * Cada resolución genera:
 *  - Auditoría de movimientos del equipo (EquipoTraceService)
 *  - Actualización de estatus en equipos y asignacion_equipos
 *  - Si hubo reemplazo: ajuste de lote_modelos_recibidos + alta del equipo nuevo
 */
class GarantiaExternaService
{
    public function __construct(
        private EquipoTraceService $trace,
    ) {}

    // ══════════════════════════════════════════════════════════════
    // CASO 1: REPARADO
    // El proveedor reparó el equipo — regresa con el mismo serial.
    // El equipo retoma el flujo normal de preparación.
    // ══════════════════════════════════════════════════════════════

    /**
     * @param  array{fecha_resolucion: string, observaciones: ?string}  $datos
     */
    public function resolverReparado(GarantiaProveedor $garantia, array $datos): void
    {
        $this->asegurarPendiente($garantia);

        DB::transaction(function () use ($garantia, $datos) {
            $equipo = $garantia->equipo;

            // Auditoría antes de cambiar
            $this->trace->registrarAuditoria($equipo, 'GARANTIA_REPARADA', Auth::id(), [
                'garantia_id'   => $garantia->id,
                'proveedor_id'  => $garantia->proveedor_id,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            // El equipo regresa a ASIGNADO para que el técnico lo inicie nuevamente
            $equipo->update([
                'estatus_ciclo' => Equipo::CICLO_PREPARACION,
                'estatus_area'  => Equipo::AREA_ASIGNADO,
                'almacen_id'    => \App\Models\Almacen::PREPARACION,
                'registrado_por_user_id' => $datos['tecnico_reingreso_id'],
            ]);

            // --------------------------------------------------
            // Reasignar al técnico elegido
            // --------------------------------------------------
            $asignacionPadre = AsignacionEquipo::find($garantia->asignacion_equipo_id)?->asignacion;

            if ($asignacionPadre) {
                // Crear un nuevo asignacion_equipo en la misma asignación padre
                AsignacionEquipo::create([
                    'asignacion_id' => $asignacionPadre->id,
                    'equipo_id'     => $equipo->id,
                    'camino'        => AsignacionEquipo::PENDIENTE,
                    'inicio_en'     => null,
                    'fin_en'        => null,
                    'notas'         => 'Equipo reparado por garantía externa #'.$garantia->id,
                ]);

                // Si el técnico de reingreso es diferente al de la asignación padre,
                // O si la asignación padre ya está terminada/cancelada,
                // crear una nueva asignación individual
                if (in_array($asignacionPadre->estatus, [\App\Models\Asignacion::ENTREGADO, \App\Models\Asignacion::CANCELADO]) || (int) $datos['tecnico_reingreso_id'] !== (int) $asignacionPadre->tecnico_id) {
                    $nuevaAsignacion = Asignacion::create([
                        'tecnico_id'    => $datos['tecnico_reingreso_id'],
                        'asignado_por_id' => Auth::id(),
                        'lote_modelo_id' => $equipo->lote_modelo_id,
                        'cantidad'      => 1,
                        'estatus'       => Asignacion::EN_PROCESO,
                        'fecha_asignacion' => now(),
                        'notas'         => 'Asignación por reparación de garantía externa #'.$garantia->id,
                    ]);

                    // Mover el asignacion_equipo recién creado a esta nueva asignación
                    AsignacionEquipo::where('equipo_id', $equipo->id)
                        ->latest('id')
                        ->first()
                        ?->update(['asignacion_id' => $nuevaAsignacion->id]);
                }
            }

            // Cerrar la garantía
            $garantia->update([
                'estatus'              => GarantiaProveedor::RESUELTA,
                'tipo_resolucion'      => GarantiaProveedor::REPARADO,
                'tecnico_reingreso_id' => $datos['tecnico_reingreso_id'],
                'resuelto_por_id'      => Auth::id(),
                'fecha_resolucion'     => $datos['fecha_resolucion'],
                'observaciones_resolucion' => $datos['observaciones'] ?? null,
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════
    // CASO 2: REEMPLAZADO
    // El proveedor entrega un equipo nuevo (mismo o diferente modelo).
    // Se ajustan lote_modelos_recibidos, se da de alta el equipo nuevo
    // y se crea una nueva asignación para el técnico elegido.
    // ══════════════════════════════════════════════════════════════

    /**
     * @param  array{
     *   numero_serie_nuevo: string,
     *   lote_modelo_nuevo_id: ?int,
     *   mismo_modelo: bool,
     *   nuevo_modelo_marca: ?string,
     *   nuevo_modelo_modelo: ?string,
     *   nuevo_modelo_clasificacion_id: ?int,
     *   tecnico_reingreso_id: int,
     *   fecha_resolucion: string,
     *   observaciones: ?string,
     * } $datos
     */
    public function resolverReemplazado(GarantiaProveedor $garantia, array $datos): void
    {
        $this->asegurarPendiente($garantia);

        $equipoOriginal   = $garantia->equipo;
        $loteModeloViejo  = LoteModeloRecibido::findOrFail($equipoOriginal->lote_modelo_id);

        DB::transaction(function () use ($garantia, $datos, $equipoOriginal, $loteModeloViejo) {

            // --------------------------------------------------
            // 1. Determinar el lote_modelo del equipo nuevo
            // --------------------------------------------------
            if ($datos['mismo_modelo']) {
                // Mismo modelo → misma línea de lote, no se toca cantidad
                $loteModeloNuevo = $loteModeloViejo;
            } else {
                // Modelo diferente → buscar o crear línea en el mismo lote
                $loteModeloNuevo = $this->resolverOCrearLoteModelo(
                    loteId:          $loteModeloViejo->lote_id,
                    loteModeloId:    $datos['lote_modelo_nuevo_id'],
                    marca:           $datos['nuevo_modelo_marca'] ?? null,
                    modelo:          $datos['nuevo_modelo_modelo'] ?? null,
                    clasificacionId: $datos['nuevo_modelo_clasificacion_id'] ?? null,
                );

                // Restar 1 del modelo viejo (el equipo original ya no existe en inventario)
                $loteModeloViejo->decrement('cantidad_recibida');

                // Sumar 1 al modelo nuevo (el proveedor entregó uno)
                $loteModeloNuevo->increment('cantidad_recibida');
            }

            // --------------------------------------------------
            // 2. Marcar el equipo original como GARANTIA_CAMBIO
            // --------------------------------------------------
            $this->trace->registrarAuditoria($equipoOriginal, 'GARANTIA_EQUIPO_CAMBIADO', Auth::id(), [
                'garantia_id'         => $garantia->id,
                'proveedor_id'        => $garantia->proveedor_id,
                'numero_serie_nuevo'  => $datos['numero_serie_nuevo'],
                'observaciones'       => $datos['observaciones'] ?? null,
            ]);

            $equipoOriginal->update([
                'estatus_ciclo' => Equipo::CICLO_PREPARACION,
                'estatus_area'  => Equipo::AREA_GARANTIA_CAMBIO,
            ]);

            // --------------------------------------------------
            // 3. Dar de alta el equipo nuevo
            // --------------------------------------------------
            $equipoNuevo = Equipo::create([
                'numero_serie'          => $datos['numero_serie_nuevo'],
                'lote_modelo_id'        => $loteModeloNuevo->id,
                'proveedor_id'          => $equipoOriginal->proveedor_id,
                'marca'                 => $loteModeloNuevo->marca,
                'modelo'                => $loteModeloNuevo->modelo,
                'tipo_equipo'           => $equipoOriginal->tipo_equipo,
                'estatus_ciclo'         => Equipo::CICLO_PREPARACION,
                'estatus_area'          => Equipo::AREA_ASIGNADO,
                'almacen_id'            => \App\Models\Almacen::PREPARACION,
                'clasificacion_puntos_id' => $loteModeloNuevo->clasificacion_puntos_id,
                'registrado_por_user_id'  => $datos['tecnico_reingreso_id'],
                'catalogo_equipo_id'    => $loteModeloNuevo->catalogo_equipo_id,
                'notas_generales'       => 'Equipo de reemplazo por garantía externa #'.$garantia->id,
            ]);

            // Auditoría del equipo nuevo
            $this->trace->registrarAuditoria($equipoNuevo, 'ALTA_GARANTIA_REEMPLAZO', Auth::id(), [
                'garantia_id'        => $garantia->id,
                'equipo_original_id' => $equipoOriginal->id,
            ]);

            // --------------------------------------------------
            // 4. Crear asignación directa al técnico elegido
            // --------------------------------------------------
            // Buscar la asignación padre original (misma macro-asignación del equipo original)
            $asignacionPadre = AsignacionEquipo::find($garantia->asignacion_equipo_id)?->asignacion;

            if ($asignacionPadre) {
                // Crear un nuevo asignacion_equipo en la misma asignación padre
                AsignacionEquipo::create([
                    'asignacion_id' => $asignacionPadre->id,
                    'equipo_id'     => $equipoNuevo->id,
                    'camino'        => AsignacionEquipo::PENDIENTE,
                    'inicio_en'     => null,
                    'fin_en'        => null,
                    'notas'         => 'Equipo de reemplazo por garantía externa #'.$garantia->id,
                ]);

                // Si el técnico de reingreso es diferente al de la asignación padre,
                // O si la asignación padre ya está terminada/cancelada,
                // crear una nueva asignación individual
                if (in_array($asignacionPadre->estatus, [\App\Models\Asignacion::ENTREGADO, \App\Models\Asignacion::CANCELADO]) || (int) $datos['tecnico_reingreso_id'] !== (int) $asignacionPadre->tecnico_id) {
                    $nuevaAsignacion = Asignacion::create([
                        'tecnico_id'    => $datos['tecnico_reingreso_id'],
                        'asignado_por_id' => Auth::id(),
                        'lote_modelo_id' => $loteModeloNuevo->id,
                        'cantidad'      => 1,
                        'estatus'       => Asignacion::EN_PROCESO,
                        'fecha_asignacion' => now(),
                        'notas'         => 'Asignación por reemplazo de garantía externa #'.$garantia->id,
                    ]);

                    // Mover el asignacion_equipo recién creado a esta nueva asignación
                    AsignacionEquipo::where('equipo_id', $equipoNuevo->id)
                        ->latest('id')
                        ->first()
                        ?->update(['asignacion_id' => $nuevaAsignacion->id]);
                }
            }

            // --------------------------------------------------
            // 5. Cerrar la garantía
            // --------------------------------------------------
            $garantia->update([
                'estatus'                  => GarantiaProveedor::RESUELTA,
                'tipo_resolucion'          => GarantiaProveedor::REEMPLAZADO,
                'numero_serie_nuevo'       => $datos['numero_serie_nuevo'],
                'equipo_nuevo_id'          => $equipoNuevo->id,
                'lote_modelo_nuevo_id'     => $loteModeloNuevo->id,
                'tecnico_reingreso_id'     => $datos['tecnico_reingreso_id'],
                'resuelto_por_id'          => Auth::id(),
                'fecha_resolucion'         => $datos['fecha_resolucion'],
                'observaciones_resolucion' => $datos['observaciones'] ?? null,
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════
    // CASO 3: RECHAZADO POR PROVEEDOR
    // El proveedor no cubrió la garantía. El equipo regresa sin
    // reparar. El técnico deberá decidir qué hacer a continuación.
    // ══════════════════════════════════════════════════════════════

    /**
     * @param  array{fecha_resolucion: string, observaciones: ?string}  $datos
     */
    public function resolverRechazado(GarantiaProveedor $garantia, array $datos): void
    {
        $this->asegurarPendiente($garantia);

        DB::transaction(function () use ($garantia, $datos) {
            $equipo = $garantia->equipo;

            $this->trace->registrarAuditoria($equipo, 'GARANTIA_RECHAZADA_PROVEEDOR', Auth::id(), [
                'garantia_id'   => $garantia->id,
                'proveedor_id'  => $garantia->proveedor_id,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            // Regresa a EN_PROCESO — el técnico decide el siguiente paso
            $equipo->update([
                'estatus_ciclo' => Equipo::CICLO_PREPARACION,
                'estatus_area'  => Equipo::AREA_EN_PROCESO,
                'almacen_id'    => \App\Models\Almacen::PREPARACION,
            ]);

            AsignacionEquipo::where('id', $garantia->asignacion_equipo_id)
                ->where('camino', 'GARANTIA_EXTERNA')
                ->update(['camino' => AsignacionEquipo::EN_PROCESO, 'fin_en' => null]);

            $garantia->update([
                'estatus'                  => GarantiaProveedor::RESUELTA,
                'tipo_resolucion'          => GarantiaProveedor::RECHAZADO_PROVEEDOR,
                'resuelto_por_id'          => Auth::id(),
                'fecha_resolucion'         => $datos['fecha_resolucion'],
                'observaciones_resolucion' => $datos['observaciones'] ?? null,
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVADOS / HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Busca un lote_modelo existente por ID, o crea uno nuevo en el mismo
     * lote si se proporcionan marca, modelo y clasificación.
     */
    private function resolverOCrearLoteModelo(
        int    $loteId,
        ?int   $loteModeloId,
        ?string $marca,
        ?string $modelo,
        ?int   $clasificacionId
    ): LoteModeloRecibido {
        if ($loteModeloId) {
            return LoteModeloRecibido::findOrFail($loteModeloId);
        }

        // No existe → crear la línea automáticamente en el mismo lote
        if (! $marca || ! $modelo) {
            throw new \RuntimeException('Se debe especificar marca y modelo para crear una nueva línea de lote.');
        }

        return LoteModeloRecibido::create([
            'lote_id'               => $loteId,
            'marca'                 => $marca,
            'modelo'                => $modelo,
            'cantidad_recibida'     => 0, // Se incrementará en el método principal
            'clasificacion_puntos_id' => $clasificacionId,
        ]);
    }

    private function asegurarPendiente(GarantiaProveedor $garantia): void
    {
        if (! $garantia->esPendiente()) {
            throw new \RuntimeException('Esta garantía ya fue resuelta o cancelada.');
        }
    }
}
