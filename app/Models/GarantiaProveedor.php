<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de una garantía externa (de proveedor) gestionada por el área
 * de preparación. Captura el ciclo completo:
 *
 *  PENDIENTE  → técnico reportó el defecto, se envió al proveedor
 *  RESUELTA   → proveedor respondió (reparó, reemplazó o rechazó)
 *  CANCELADA  → se canceló antes de resolver
 *
 * Historial de auditoría:
 *  - reportado_por_id   → quién mandó el equipo a garantía
 *  - resuelto_por_id    → quién registró la resolución en el sistema
 *  - tecnico_reingreso_id → a quién se le asignó el equipo nuevo (si aplica)
 */
class GarantiaProveedor extends Model
{
    use HasFactory;

    protected $table = 'garantias_externas';

    protected $guarded = [];

    // =========================================================
    // CONSTANTES DE ESTATUS
    // =========================================================

    const PENDIENTE  = 'PENDIENTE';
    const RESUELTA   = 'RESUELTA';
    const CANCELADA  = 'CANCELADA';

    // =========================================================
    // CONSTANTES DE TIPO DE RESOLUCIÓN
    // =========================================================

    /** El proveedor reparó el mismo equipo — regresa con el mismo serial */
    const REPARADO            = 'REPARADO';

    /** El proveedor entregó un equipo nuevo (puede ser mismo o diferente modelo) */
    const REEMPLAZADO         = 'REEMPLAZADO';

    /** El proveedor no cubrió la garantía — el equipo regresa sin reparar */
    const RECHAZADO_PROVEEDOR = 'RECHAZADO_PROVEEDOR';

    // =========================================================
    // HELPERS
    // =========================================================

    public function esPendiente(): bool
    {
        return $this->estatus === self::PENDIENTE;
    }

    public function esResuelta(): bool
    {
        return $this->estatus === self::RESUELTA;
    }

    public function huboReemplazo(): bool
    {
        return $this->tipo_resolucion === self::REEMPLAZADO;
    }

    // =========================================================
    // RELACIONES
    // =========================================================

    /** Equipo original que se mandó al proveedor */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id')->withTrashed();
    }

    /** Equipo nuevo generado por el reemplazo (si aplica) */
    public function equipoNuevo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_nuevo_id');
    }

    /** Proveedor que atendió la garantía */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /** Asignación del técnico donde ocurrió el problema */
    public function asignacionEquipo(): BelongsTo
    {
        return $this->belongsTo(AsignacionEquipo::class, 'asignacion_equipo_id');
    }

    /** Técnico que detectó el problema y reportó la garantía */
    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_id');
    }

    /** Usuario que registró la resolución en el sistema */
    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por_id');
    }

    /** Técnico al que se asignó el equipo nuevo */
    public function tecnicoReingreso(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_reingreso_id');
    }

    /** Modelo de lote al que pertenece el equipo nuevo (puede diferir del original) */
    public function loteModeloNuevo(): BelongsTo
    {
        return $this->belongsTo(LoteModeloRecibido::class, 'lote_modelo_nuevo_id');
    }

    // =========================================================
    // CASTS
    // =========================================================

    protected $casts = [
        'fecha_envio'       => 'date',
        'fecha_resolucion'  => 'date',
    ];
}
