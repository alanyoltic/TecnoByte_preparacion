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
        'estatus',
        'inventario_pieza_id',
        'respondida_por_id',
        'respondida_en',
        'notas_respuesta',
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
        'instalada' => 'boolean',
        'funciono' => 'boolean',
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
        return $this->belongsTo(User::class, 'solicitado_por_id');
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
        return $this->belongsTo(User::class, 'respondida_por_id');
    }

    public function reasignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reasignado_a_id');
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
    public function surtirDeInventario(int $inventarioPiezaId, int $gerenteId, ?string $notas = null, ?int $tecnicoReasignadoId = null): void
    {
        DB::transaction(function () use ($inventarioPiezaId, $gerenteId, $notas, $tecnicoReasignadoId) {
            $pieza = InventarioPieza::lockForUpdate()->findOrFail($inventarioPiezaId);

            if ($pieza->cantidad_disponible < 1) {
                throw new \Exception('No hay unidades disponibles de esta pieza en el stock seleccionado.');
            }

            $this->update([
                'inventario_pieza_id' => $inventarioPiezaId,
                'estatus'             => self::SURTIDA_INVENTARIO,
                'respondida_por_id'   => $gerenteId,
                'respondida_en'       => now(),
                'notas_respuesta'     => $notas,
                'reasignado_a_id'     => $tecnicoReasignadoId,
                'reasignada_en'       => $tecnicoReasignadoId ? now() : null,
            ]);

            $pieza->decrement('cantidad_disponible');
            $pieza->increment('cantidad_reservada');
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

            // Si había pieza asignada, devolver la unidad al stock
            if ($this->inventario_pieza_id) {
                $pieza = InventarioPieza::find($this->inventario_pieza_id);
                if ($pieza && $pieza->cantidad_reservada > 0) {
                    $pieza->decrement('cantidad_reservada');
                    $pieza->increment('cantidad_disponible');
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
                if ($pieza && $pieza->cantidad_reservada > 0) {
                    $pieza->decrement('cantidad_reservada');

                    if ($funciono) {
                        $pieza->increment('cantidad_usada');
                        // Vincular al equipo si la entrada es de una sola unidad (trazabilidad)
                        $equipoId = $this->equipo_id ?? $this->asignacionEquipo?->equipo_id;
                        if ($equipoId && $pieza->cantidad_inicial === 1) {
                            $pieza->update(['equipo_destino_id' => $equipoId]);
                        }
                    } else {
                        $pieza->increment('cantidad_baja');
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
                    'almacen_id'    => 5, // almacén Calidad
                ]);
            } elseif ($equipo && !$funciono) {
                self::create([
                    'asignacion_equipo_id' => $this->asignacion_equipo_id,
                    'equipo_id'            => $this->equipo_id,
                    'solicitado_por_id'    => $tecnicoId,
                    'catalogo_pieza_id'    => $this->catalogo_pieza_id,
                    'descripcion_libre'    => $this->descripcion_libre ?: 'Reintento - pieza anterior defectuosa',
                    'estatus'              => self::PENDIENTE,
                ]);
            }

            if ($funciono && $this->asignacion_equipo_id) {
                $clasificacionId = $equipo?->clasificacion_puntos_id;
                $puntosBase = $clasificacionId
                    ? (ClasificacionPuntos::find($clasificacionId)?->puntos_base ?? 1.0)
                    : 1.0;

                PuntoTecnico::registrar(
                    tecnicoId: $tecnicoId,
                    asignacionEquipoId: $this->asignacion_equipo_id,
                    rol: PuntoTecnico::TERMINO_PIEZA,
                    puntosBase: (float) $puntosBase,
                    clasificacionId: $clasificacionId,
                );
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
