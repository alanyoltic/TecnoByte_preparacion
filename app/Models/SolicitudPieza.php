<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SolicitudPieza extends Model
{
    protected $table = 'solicitudes_piezas';

    protected $fillable = [
        'asignacion_equipo_id',
        'equipo_id',
        'solicitado_por_id',
        'catalogo_pieza_id',
        'descripcion_libre',
        'categoria_solicitada',
        'detalle_solicitado',
        'cantidad',
        'requiere_cable_bateria',
        'estatus',
        'inventario_pieza_id',
        'respondida_por_id',
        'respondida_en',
        'notas_respuesta',
        'puntos_override',
        'reasignado_a_id',
        'reasignada_en',
        'iniciada_instalacion_en',
        'instalada',
        'funciono',
        'confirmada_en',
        'notas_confirmacion',
    ];

    protected $casts = [
        'respondida_en' => 'datetime',
        'reasignada_en' => 'datetime',
        'iniciada_instalacion_en' => 'datetime',
        'confirmada_en' => 'datetime',
        'instalada' => 'boolean',
        'funciono' => 'boolean',
        'cantidad' => 'integer',
        'requiere_cable_bateria' => 'boolean',
    ];

    const PENDIENTE = 'PENDIENTE';

    const SURTIDA_INVENTARIO = 'SURTIDA_INVENTARIO';

    const PENDIENTE_COMPRA = 'PENDIENTE_COMPRA';

    const COMPRADA = 'COMPRADA';

    const CANCELADA = 'CANCELADA';

    const CONFIRMADA = 'CONFIRMADA';

    const REQUIERE_REASIGNACION = 'REQUIERE_REASIGNACION';

    const ESTATUS_ACTIVOS = [
        self::PENDIENTE,
        self::PENDIENTE_COMPRA,
        self::COMPRADA,
        self::SURTIDA_INVENTARIO,
        self::REQUIERE_REASIGNACION,
    ];

    /**
     * Relaciones existentes
     */
    public function asignacionEquipo(): BelongsTo
    {
        return $this->belongsTo(AsignacionEquipo::class, 'asignacion_equipo_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_id')->withoutGlobalScopes();
    }

    public function catalogoPieza(): BelongsTo
    {
        return $this->belongsTo(CatalogoPieza::class, 'catalogo_pieza_id');
    }

    public function inventarioPieza(): BelongsTo
    {
        return $this->belongsTo(InventarioPieza::class, 'inventario_pieza_id');
    }

    public function respondidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondida_por_id')->withoutGlobalScopes();
    }

    public function reasignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reasignado_a_id')->withoutGlobalScopes();
    }

    public function intentos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SolicitudPiezaIntento::class, 'solicitud_pieza_id')->orderBy('numero_intento');
    }

    public function intentoActual(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SolicitudPiezaIntento::class, 'solicitud_pieza_id')->latestOfMany('numero_intento');
    }

    /** Minutos que tardó la instalación (inicio → confirmación). */
    public function minutosInstalacion(): int
    {
        if (! $this->iniciada_instalacion_en) {
            return 0;
        }
        $fin = $this->confirmada_en ?? now();

        return (int) $this->iniciada_instalacion_en->diffInMinutes($fin);
    }

    public function fueIniciada(): bool
    {
        return $this->iniciada_instalacion_en !== null;
    }

    /**
     * Scopes existentes
     */
    public function scopePendientes($query)
    {
        return $query->where('estatus', self::PENDIENTE);
    }

    public function scopePendientesCompra($query)
    {
        return $query->where('estatus', self::PENDIENTE_COMPRA);
    }

    /**
     * Nuevos Scopes
     */
    public function scopeSurtidas($query)
    {
        return $query->where('estatus', self::SURTIDA_INVENTARIO);
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estatus', self::CONFIRMADA);
    }

    public function scopePendientesConfirmacion($query)
    {
        return $query->where('estatus', self::SURTIDA_INVENTARIO)
            ->where('instalada', false);
    }

    public function scopeDelTecnico($query, $tecnicoId)
    {
        return $query->where('solicitado_por_id', $tecnicoId);
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estatus', self::ESTATUS_ACTIVOS);
    }

    /**
     * Métodos existentes
     */
    public function estaResuelta(): bool
    {
        return in_array($this->estatus, [
            self::SURTIDA_INVENTARIO,
            self::COMPRADA,
            self::CANCELADA,
            self::CONFIRMADA,
        ]);
    }

    public function getNombrePiezaAttribute(): string
    {
        return $this->catalogoPieza?->nombre ?? $this->descripcion_libre ?? 'Sin descripción';
    }

    public static function formatearDescripcionSolicitada(?string $categoria, ?string $detalle): ?string
    {
        $categoria = trim((string) $categoria);
        $detalle = trim((string) $detalle);

        if ($categoria === '' && $detalle === '') {
            return null;
        }

        if ($categoria === '') {
            return $detalle;
        }

        if ($detalle === '') {
            return $categoria;
        }

        return $categoria.' — '.$detalle;
    }

    public static function parsearDescripcionLibre(?string $descripcion): array
    {
        $descripcion = trim((string) $descripcion);

        if ($descripcion === '') {
            return ['categoria' => null, 'detalle' => null];
        }

        if (preg_match('/^\[(.+?)\]\s*(.*)$/u', $descripcion, $matches) === 1) {
            return [
                'categoria' => trim((string) ($matches[1] ?? '')) ?: null,
                'detalle' => trim((string) ($matches[2] ?? '')) ?: null,
            ];
        }

        $partes = preg_split('/\s+[—–-]\s+/u', $descripcion, 2);

        if (is_array($partes) && count($partes) === 2) {
            return [
                'categoria' => trim((string) ($partes[0] ?? '')) ?: null,
                'detalle' => trim((string) ($partes[1] ?? '')) ?: null,
            ];
        }

        return [
            'categoria' => trim($descripcion) ?: null,
            'detalle' => null,
        ];
    }

    public function getCategoriaSolicitadaTextoAttribute(): ?string
    {
        $categoria = trim((string) ($this->attributes['categoria_solicitada'] ?? ''));

        if ($categoria !== '') {
            return $categoria;
        }

        return static::parsearDescripcionLibre($this->attributes['descripcion_libre'] ?? null)['categoria'];
    }

    public function getDetalleSolicitadoTextoAttribute(): ?string
    {
        $detalle = trim((string) ($this->attributes['detalle_solicitado'] ?? ''));

        if ($detalle !== '') {
            return $detalle;
        }

        return static::parsearDescripcionLibre($this->attributes['descripcion_libre'] ?? null)['detalle'];
    }

    public function getDescripcionSolicitadaAttribute(): ?string
    {
        $descripcion = static::formatearDescripcionSolicitada(
            $this->categoria_solicitada_texto,
            $this->detalle_solicitado_texto
        );

        if ($descripcion !== null) {
            return $descripcion;
        }

        $legacy = trim((string) ($this->attributes['descripcion_libre'] ?? ''));

        return $legacy !== '' ? $legacy : null;
    }

    public function getTituloSolicitudAttribute(): string
    {
        return $this->catalogoPieza?->nombre
            ?? $this->descripcion_solicitada
            ?? $this->descripcion_libre
            ?? 'Sin descripción';
    }

    public static function labelsEstatus(): array
    {
        return [
            self::PENDIENTE => 'Pendiente',
            self::SURTIDA_INVENTARIO => 'Surtida del Inventario',
            self::PENDIENTE_COMPRA => 'Pendiente de Compra',
            self::COMPRADA => 'Comprada',
            self::CANCELADA => 'Cancelada',
            self::CONFIRMADA => 'Instalada y Confirmada',
            self::REQUIERE_REASIGNACION => 'Reasignación pendiente',
        ];
    }

    /**
     * Nuevos métodos para gestión completa
     */

    /**
     * Verificar si puede ser gestionada por gerente
     */
    public function puedeSerGestionada(): bool
    {
        return in_array($this->estatus, [self::PENDIENTE, self::REQUIERE_REASIGNACION], true);
    }

    public function puedeSerSurtidaDesdeInventario(): bool
    {
        return in_array($this->estatus, [
            self::PENDIENTE,
            self::PENDIENTE_COMPRA,
            self::COMPRADA,
            self::REQUIERE_REASIGNACION,
        ], true);
    }

    public function puedeCancelarse(): bool
    {
        return in_array($this->estatus, [
            self::PENDIENTE,
            self::PENDIENTE_COMPRA,
            self::COMPRADA,
            self::SURTIDA_INVENTARIO,
            self::REQUIERE_REASIGNACION,
        ], true);
    }

    /**
     * Verificar si puede ser confirmada por técnico
     */
    public function puedeSerConfirmada(): bool
    {
        return $this->estatus === self::SURTIDA_INVENTARIO && ! $this->instalada;
    }

    /**
     * Surtir pieza del inventario
     */
    public function surtirDeInventario(int $inventarioPiezaId, int $gerenteId, ?string $notas = null, ?int $tecnicoReasignadoId = null, ?float $puntosOverride = null): void
    {
        DB::transaction(function () use ($inventarioPiezaId, $gerenteId, $notas, $tecnicoReasignadoId, $puntosOverride) {
            $pieza = InventarioPieza::lockForUpdate()->findOrFail($inventarioPiezaId);
            $cantidad = max(1, (int) $this->cantidad);

            if ($pieza->cantidad_disponible < $cantidad) {
                throw new \Exception(
                    "Stock insuficiente: se necesitan {$cantidad} unidad(es) pero solo hay {$pieza->cantidad_disponible} disponible(s)."
                );
            }

            $this->update([
                'inventario_pieza_id' => $inventarioPiezaId,
                'estatus' => self::SURTIDA_INVENTARIO,
                'respondida_por_id' => $gerenteId,
                'respondida_en' => now(),
                'notas_respuesta' => $notas,
                'puntos_override' => $puntosOverride > 0 ? $puntosOverride : null,
                'reasignado_a_id' => $tecnicoReasignadoId,
                'reasignada_en' => $tecnicoReasignadoId ? now() : null,
                'iniciada_instalacion_en' => null,
                'funciono' => null,
                'confirmada_en' => null,
                'notas_confirmacion' => null,
                'instalada' => false,
            ]);

            $numeroIntento = $this->intentos()->count() + 1;
            $this->intentos()->create([
                'numero_intento' => $numeroIntento,
                'inventario_pieza_id' => $inventarioPiezaId,
                'asignado_a_id' => $tecnicoReasignadoId ?? $this->solicitado_por_id,
                'asignado_por_id' => $gerenteId,
                'asignado_en' => now(),
                'puntos_override' => $puntosOverride > 0 ? $puntosOverride : null,
                'notas_asignacion' => $notas,
            ]);

            $pieza->decrement('cantidad_disponible', $cantidad);
            $pieza->increment('cantidad_reservada', $cantidad);
            $pieza->actualizarEstatus();
        });
    }

    /**
     * Marcar como pendiente de compra
     */
    public function marcarPendienteCompra(int $gerenteId, ?string $notas = null): void
    {
        $this->update([
            'estatus' => self::PENDIENTE_COMPRA,
            'respondida_por_id' => $gerenteId,
            'respondida_en' => now(),
            'notas_respuesta' => $notas,
        ]);
    }

    public function marcarComoComprada(int $gerenteId, ?string $notas = null): void
    {
        $this->update([
            'estatus' => self::COMPRADA,
            'respondida_por_id' => $gerenteId,
            'respondida_en' => now(),
            'notas_respuesta' => $notas,
        ]);
    }

    /**
     * Cancelar solicitud
     */
    public function cancelar(int $gerenteId, string $motivo): void
    {
        DB::transaction(function () use ($gerenteId, $motivo) {
            $this->update([
                'estatus' => self::CANCELADA,
                'respondida_por_id' => $gerenteId,
                'respondida_en' => now(),
                'notas_respuesta' => $motivo,
            ]);

            // Si había pieza asignada, devolver las unidades reservadas al stock
            if ($this->inventario_pieza_id) {
                $pieza = InventarioPieza::find($this->inventario_pieza_id);
                $cantidad = max(1, (int) $this->cantidad);
                if ($pieza && $pieza->cantidad_reservada > 0) {
                    $devolver = min($cantidad, $pieza->cantidad_reservada);
                    $pieza->decrement('cantidad_reservada', $devolver);
                    $pieza->increment('cantidad_disponible', $devolver);
                    $pieza->actualizarEstatus();
                }
            }
        });
    }

    /**
     * Confirmar instalación (técnico)
     */
    public function confirmarInstalacion(bool $funciono, ?string $notas = null): void
    {
        DB::transaction(function () use ($funciono, $notas) {
            $this->update([
                'estatus' => self::CONFIRMADA,
                'instalada' => true,
                'funciono' => $funciono,
                'confirmada_en' => now(),
                'notas_confirmacion' => $notas,
            ]);

            if ($this->inventario_pieza_id) {
                $pieza = InventarioPieza::lockForUpdate()->find($this->inventario_pieza_id);
                $cantidad = max(1, (int) $this->cantidad);

                if ($pieza && $pieza->cantidad_reservada > 0) {
                    $mover = min($cantidad, $pieza->cantidad_reservada);
                    $pieza->decrement('cantidad_reservada', $mover);

                    if ($funciono) {
                        $pieza->increment('cantidad_usada', $mover);
                        // Vincular al equipo si toda la entrada fue para este equipo (trazabilidad)
                        $equipoId = $this->equipo_id ?? $this->asignacionEquipo?->equipo_id;
                        if ($equipoId && $pieza->cantidad_inicial === $cantidad) {
                            $pieza->update(['equipo_destino_id' => $equipoId]);
                        }
                    } else {
                        $pieza->increment('cantidad_baja', $mover);
                    }

                    $pieza->actualizarEstatus();
                }
            }
        });
    }

    public function finalizarInstalacionPorTecnico(int $tecnicoId, bool $funciono, ?string $notas = null): void
    {
        DB::transaction(function () use ($tecnicoId, $funciono, $notas) {
            $ahora = now();

            if (! $this->iniciada_instalacion_en) {
                $this->update(['iniciada_instalacion_en' => $ahora]);
                $this->refresh();
            }

            // Actualizar el intento actual con el resultado
            $intento = $this->intentoActual;
            if ($intento) {
                $intento->update([
                    'iniciada_instalacion_en' => $this->iniciada_instalacion_en,
                    'confirmada_en' => $ahora,
                    'funciono' => $funciono,
                    'notas_confirmacion' => $notas,
                ]);
            }

            $equipo = $this->equipo ?? $this->asignacionEquipo?->equipo;
            $cantidad = max(1, (int) $this->cantidad);

            if ($funciono) {
                // Éxito: confirmar solicitud y mover pieza a USADA
                $this->update([
                    'estatus' => self::CONFIRMADA,
                    'instalada' => true,
                    'funciono' => true,
                    'confirmada_en' => $ahora,
                    'notas_confirmacion' => $notas,
                ]);

                if ($this->inventario_pieza_id) {
                    $pieza = InventarioPieza::lockForUpdate()->find($this->inventario_pieza_id);
                    if ($pieza && $pieza->cantidad_reservada > 0) {
                        $mover = min($cantidad, $pieza->cantidad_reservada);
                        $pieza->decrement('cantidad_reservada', $mover);
                        $pieza->increment('cantidad_usada', $mover);
                        $equipoId = $this->equipo_id ?? $this->asignacionEquipo?->equipo_id;
                        if ($equipoId && $pieza->cantidad_inicial === $cantidad) {
                            $pieza->update(['equipo_destino_id' => $equipoId]);
                        }
                        $pieza->actualizarEstatus();
                    }
                }

                if ($equipo) {
                    $equipo->update([
                        'estatus_ciclo' => 'CALIDAD',
                        'estatus_area' => 'EN_CALIDAD',
                        'almacen_id' => Almacen::CALIDAD,
                    ]);
                }

                // Registrar puntos de pieza si el gerente asignó puntos_override
                if ($intento && $intento->puntos_override && $intento->puntos_override > 0 && $this->asignacion_equipo_id) {
                    PuntoTecnico::registrar(
                        tecnicoId: $tecnicoId,
                        asignacionEquipoId: $this->asignacion_equipo_id,
                        rol: PuntoTecnico::PIEZA_COMPLETADA,
                        puntosBase: (float) $intento->puntos_override,
                        clasificacionId: null, // Piezas no usan clasificación, solo puntos_override
                    );
                }

                // Marcar el registro de trabajo como EN_CALIDAD
                if ($this->asignacion_equipo_id) {
                    AsignacionEquipo::where('id', $this->asignacion_equipo_id)
                        ->where('camino', AsignacionEquipo::PIEZA_PENDIENTE)
                        ->update(['camino' => AsignacionEquipo::EN_CALIDAD]);
                }
            } else {
                // Fallo: devolver pieza al inventario como baja y resetear solicitud a PENDIENTE
                if ($this->inventario_pieza_id) {
                    $pieza = InventarioPieza::lockForUpdate()->find($this->inventario_pieza_id);
                    if ($pieza && $pieza->cantidad_reservada > 0) {
                        $devolver = min($cantidad, $pieza->cantidad_reservada);
                        $pieza->decrement('cantidad_reservada', $devolver);
                        $pieza->increment('cantidad_baja', $devolver);
                        $pieza->actualizarEstatus();
                    }
                }

                // Marcar como REQUIERE_REASIGNACION para que el gerente asigne otra pieza
                $this->update([
                    'estatus' => self::REQUIERE_REASIGNACION,
                    'inventario_pieza_id' => null,
                    'respondida_por_id' => null,
                    'respondida_en' => null,
                    'notas_respuesta' => null,
                    'puntos_override' => null,
                    'reasignado_a_id' => null,
                    'reasignada_en' => null,
                    'iniciada_instalacion_en' => null,
                    'instalada' => false,
                    'funciono' => null,
                    'confirmada_en' => null,
                    'notas_confirmacion' => null,
                ]);

                // Equipo se queda en PENDIENTE_PIEZA (no cambia)
            }
        });
    }

    /**
     * Obtener color del badge según estado
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->estatus) {
            self::PENDIENTE => 'yellow',
            self::SURTIDA_INVENTARIO => 'blue',
            self::PENDIENTE_COMPRA => 'orange',
            self::COMPRADA => 'purple',
            self::CONFIRMADA => 'green',
            self::CANCELADA => 'red',
            self::REQUIERE_REASIGNACION => 'rose',
            default => 'gray',
        };
    }

    /**
     * Obtener ícono según estado
     */
    public function getIconoEstadoAttribute(): string
    {
        return match ($this->estatus) {
            self::PENDIENTE => '⏳',
            self::SURTIDA_INVENTARIO => '📦',
            self::PENDIENTE_COMPRA => '🛒',
            self::COMPRADA => '✅',
            self::CONFIRMADA => '✓',
            self::CANCELADA => '✗',
            self::REQUIERE_REASIGNACION => '↩',
            default => '•',
        };
    }

    /**
     * Verificar si está en flujo de compra
     */
    public function esCompra(): bool
    {
        return in_array($this->estatus, [
            self::PENDIENTE_COMPRA,
            self::COMPRADA,
        ]);
    }

    /**
     * Obtener equipo (compatibilidad con ambos sistemas)
     */
    public function getEquipoRelacionadoAttribute(): ?Equipo
    {
        return $this->equipo ?? $this->asignacionEquipo?->equipo;
    }

    /**
     * Obtener técnico solicitante
     */
    public function getTecnicoAttribute(): ?User
    {
        return $this->solicitadoPor;
    }

    /**
     * Obtener gerente que respondió
     */
    public function getGerenteAttribute(): ?User
    {
        return $this->respondidaPor;
    }
}
