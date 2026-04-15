<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Almacen;

class SolicitudPieza extends Model
{
    protected $table = 'solicitudes_piezas';

    protected $fillable = [
        'asignacion_equipo_id',
        'equipo_id',
        'solicitado_por_id',
        'catalogo_pieza_id',
        'descripcion_libre',
        'cantidad',
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
        'respondida_en'           => 'datetime',
        'reasignada_en'           => 'datetime',
        'iniciada_instalacion_en' => 'datetime',
        'confirmada_en'           => 'datetime',
        'instalada'               => 'boolean',
        'funciono'                => 'boolean',
        'cantidad'                => 'integer',
    ];

    // Estados existentes
    const PENDIENTE           = 'PENDIENTE';
    const SURTIDA_INVENTARIO  = 'SURTIDA_INVENTARIO';
    const PENDIENTE_COMPRA    = 'PENDIENTE_COMPRA';
    const COMPRADA            = 'COMPRADA';
    const CANCELADA           = 'CANCELADA';
    
    // Nuevo estado para confirmación
    const CONFIRMADA          = 'CONFIRMADA';

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

    /** Minutos que tardó la instalación (inicio → confirmación). */
    public function minutosInstalacion(): int
    {
        if (!$this->iniciada_instalacion_en) return 0;
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

    public static function labelsEstatus(): array
    {
        return [
            self::PENDIENTE          => 'Pendiente',
            self::SURTIDA_INVENTARIO => 'Surtida del Inventario',
            self::PENDIENTE_COMPRA   => 'Pendiente de Compra',
            self::COMPRADA           => 'Comprada',
            self::CANCELADA          => 'Cancelada',
            self::CONFIRMADA         => 'Instalada y Confirmada',
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
        return $this->estatus === self::PENDIENTE;
    }

    public function puedeSerSurtidaDesdeInventario(): bool
    {
        return in_array($this->estatus, [
            self::PENDIENTE,
            self::PENDIENTE_COMPRA,
            self::COMPRADA,
        ], true);
    }

    public function puedeCancelarse(): bool
    {
        return in_array($this->estatus, [
            self::PENDIENTE,
            self::PENDIENTE_COMPRA,
            self::COMPRADA,
            self::SURTIDA_INVENTARIO,
        ], true);
    }

    /**
     * Verificar si puede ser confirmada por técnico
     */
    public function puedeSerConfirmada(): bool
    {
        return $this->estatus === self::SURTIDA_INVENTARIO && !$this->instalada;
    }

    /**
     * Surtir pieza del inventario
     */
    public function surtirDeInventario(int $inventarioPiezaId, int $gerenteId, ?string $notas = null, ?int $tecnicoReasignadoId = null, ?float $puntosOverride = null): void
    {
        DB::transaction(function () use ($inventarioPiezaId, $gerenteId, $notas, $tecnicoReasignadoId, $puntosOverride) {
            $pieza    = InventarioPieza::lockForUpdate()->findOrFail($inventarioPiezaId);
            $cantidad = max(1, (int) $this->cantidad);

            if ($pieza->cantidad_disponible < $cantidad) {
                throw new \Exception(
                    "Stock insuficiente: se necesitan {$cantidad} unidad(es) pero solo hay {$pieza->cantidad_disponible} disponible(s)."
                );
            }

            $this->update([
                'inventario_pieza_id' => $inventarioPiezaId,
                'estatus'             => self::SURTIDA_INVENTARIO,
                'respondida_por_id'   => $gerenteId,
                'respondida_en'       => now(),
                'notas_respuesta'     => $notas,
                'puntos_override'     => $puntosOverride > 0 ? $puntosOverride : null,
                'reasignado_a_id'     => $tecnicoReasignadoId,
                'reasignada_en'       => $tecnicoReasignadoId ? now() : null,
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
                $pieza    = InventarioPieza::find($this->inventario_pieza_id);
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
                $pieza    = InventarioPieza::lockForUpdate()->find($this->inventario_pieza_id);
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
            if (!$this->iniciada_instalacion_en) {
                $this->update(['iniciada_instalacion_en' => now()]);
            }

            $this->confirmarInstalacion($funciono, $notas);

            $equipo = $this->equipo ?? $this->asignacionEquipo?->equipo;

            if ($equipo && $funciono) {
                $equipo->update([
                    'estatus_ciclo' => 'CALIDAD',
                    'estatus_area'  => 'EN_CALIDAD',
                    'almacen_id'    => Almacen::CALIDAD,
                ]);
            } elseif ($equipo && !$funciono) {
                self::create([
                    'asignacion_equipo_id' => $this->asignacion_equipo_id,
                    'equipo_id'            => $this->equipo_id,
                    'solicitado_por_id'    => $tecnicoId,
                    'catalogo_pieza_id'    => $this->catalogo_pieza_id,
                    'descripcion_libre'    => $this->descripcion_libre ?: 'Reintento - pieza anterior defectuosa',
                    'cantidad'             => max(1, (int) $this->cantidad),
                    'estatus'              => self::PENDIENTE,
                ]);
            }

            if ($funciono && $this->asignacion_equipo_id) {
                // Los puntos de pieza siempre los define el gerente al asignarla.
                // Si por alguna razón no se definieron, no se registran puntos.
                if ($this->puntos_override > 0) {
                    PuntoTecnico::registrar(
                        tecnicoId: $tecnicoId,
                        asignacionEquipoId: $this->asignacion_equipo_id,
                        rol: PuntoTecnico::TERMINO_PIEZA,
                        puntosBase: (float) $this->puntos_override,
                        clasificacionId: null,
                    );
                }
            }
        });
    }

    /**
     * Obtener color del badge según estado
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->estatus) {
            self::PENDIENTE => 'yellow',
            self::SURTIDA_INVENTARIO => 'blue',
            self::PENDIENTE_COMPRA => 'orange',
            self::COMPRADA => 'purple',
            self::CONFIRMADA => 'green',
            self::CANCELADA => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtener ícono según estado
     */
    public function getIconoEstadoAttribute(): string
    {
        return match($this->estatus) {
            self::PENDIENTE => '⏳',
            self::SURTIDA_INVENTARIO => '📦',
            self::PENDIENTE_COMPRA => '🛒',
            self::COMPRADA => '✅',
            self::CONFIRMADA => '✓',
            self::CANCELADA => '✗',
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
