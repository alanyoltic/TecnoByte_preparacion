<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // =========================================================
    // CONSTANTES DE ESTATUS
    // =========================================================

    /**
     * estatus_ciclo — dónde está el equipo en su vida dentro de TecnoByte.
     * Lo ven todos los módulos del ERP.
     */
    const CICLO_CEDIS       = 'CEDIS';
    const CICLO_PREPARACION = 'PREPARACION';
    const CICLO_CALIDAD     = 'CALIDAD';
    const CICLO_VENTAS      = 'VENTAS';
    const CICLO_APARTADO    = 'APARTADO';
    const CICLO_VENDIDO     = 'VENDIDO';
    const CICLO_SCRAP       = 'SCRAP';

    /**
     * estatus_area — detalle interno dentro del área que lo tiene.
     * Preparación lo usa para saber en qué etapa está el trabajo.
     */
    const AREA_EN_ESPERA          = 'EN_ESPERA';
    const AREA_SIN_ASIGNAR        = 'SIN_ASIGNAR';
    const AREA_ASIGNADO           = 'ASIGNADO';
    const AREA_EN_PROCESO         = 'EN_PROCESO';
    const AREA_EN_CALIDAD         = 'EN_CALIDAD';
    const AREA_FINALIZADO         = 'FINALIZADO';
    const AREA_TRANSFERIDO        = 'TRANSFERIDO';
    const AREA_PENDIENTE_PIEZA    = 'PENDIENTE_PIEZA';
    const AREA_PENDIENTE_GARANTIA = 'PENDIENTE_GARANTIA';
    const AREA_PENDIENTE_DESARME  = 'PENDIENTE_DESARME';
    const AREA_GARANTIA_INT       = 'GARANTIA_INT';
    const AREA_GARANTIA_EXT       = 'GARANTIA_EXT';

    // =========================================================
    // REGLAS DE TRANSICIÓN
    // Por ahora son informativas/suaves — no bloquean el guardado.
    // En una siguiente iteración se pueden hacer duras (throw).
    // =========================================================

    /**
     * Transiciones válidas para estatus_ciclo.
     * Clave = estado actual, Valor = estados a los que puede ir.
     */
    private array $transicionesCiclo = [
        self::CICLO_CEDIS       => [self::CICLO_PREPARACION],
        self::CICLO_PREPARACION => [self::CICLO_CALIDAD, self::CICLO_VENTAS, self::CICLO_SCRAP],
        self::CICLO_CALIDAD     => [self::CICLO_VENTAS, self::CICLO_PREPARACION],
        self::CICLO_VENTAS      => [self::CICLO_APARTADO, self::CICLO_VENDIDO],
        self::CICLO_APARTADO    => [self::CICLO_VENTAS, self::CICLO_VENDIDO],
        self::CICLO_VENDIDO     => [], // Estado final
        self::CICLO_SCRAP       => [], // Estado final
    ];

    /**
     * Transiciones válidas para estatus_area dentro de preparación.
     */
    private array $transicionesArea = [
        self::AREA_EN_ESPERA          => [self::AREA_ASIGNADO, self::AREA_EN_PROCESO],
        self::AREA_SIN_ASIGNAR        => [self::AREA_ASIGNADO, self::AREA_EN_PROCESO],
        self::AREA_ASIGNADO           => [self::AREA_EN_PROCESO],
        self::AREA_EN_PROCESO         => [
            self::AREA_EN_CALIDAD,
            self::AREA_PENDIENTE_PIEZA,
            self::AREA_PENDIENTE_GARANTIA,
            self::AREA_PENDIENTE_DESARME,
            self::AREA_GARANTIA_INT,
            self::AREA_GARANTIA_EXT,
        ],
        self::AREA_EN_CALIDAD         => [self::AREA_FINALIZADO],
        self::AREA_FINALIZADO         => [self::AREA_TRANSFERIDO],
        self::AREA_TRANSFERIDO        => [], // Final para preparacion
        self::AREA_PENDIENTE_PIEZA    => [self::AREA_EN_PROCESO, self::AREA_EN_CALIDAD],
        self::AREA_PENDIENTE_GARANTIA => [self::AREA_EN_PROCESO],
        self::AREA_PENDIENTE_DESARME  => [self::AREA_EN_PROCESO],
        self::AREA_GARANTIA_INT       => [self::AREA_EN_PROCESO],
        self::AREA_GARANTIA_EXT       => [self::AREA_EN_PROCESO],
    ];

    /**
     * Verifica si el equipo puede transicionar su estatus_ciclo
     * al valor indicado según las reglas definidas.
     */
    public function puedeTransicionarCicloA(string $nuevoEstatus): bool
    {
        $permitidos = $this->transicionesCiclo[$this->estatus_ciclo] ?? [];
        return in_array($nuevoEstatus, $permitidos, true);
    }

    /**
     * Verifica si el equipo puede transicionar su estatus_area
     * al valor indicado según las reglas definidas.
     */
    public function puedeTransicionarAreaA(string $nuevoEstatus): bool
    {
        $permitidos = $this->transicionesArea[$this->estatus_area] ?? [];
        return in_array($nuevoEstatus, $permitidos, true);
    }

    /**
     * Retorna los estatus_ciclo a los que puede ir desde el actual.
     * Útil para poblar selects en la UI.
     */
    public function transicionesCicloDisponibles(): array
    {
        return $this->transicionesCiclo[$this->estatus_ciclo] ?? [];
    }

    /**
     * Retorna los estatus_area a los que puede ir desde el actual.
     */
    public function transicionesAreaDisponibles(): array
    {
        return $this->transicionesArea[$this->estatus_area] ?? [];
    }

    /**
     * Dado un estatus_area, retorna el estatus_ciclo que le corresponde.
     * Centraliza el mapeo para que cualquier parte del sistema lo use.
     */
    public static function cicloParaArea(string $estatusArea): string
    {
        return match ($estatusArea) {
            self::AREA_EN_CALIDAD,
            self::AREA_FINALIZADO,
            self::AREA_TRANSFERIDO  => self::CICLO_CALIDAD,
            default                 => self::CICLO_PREPARACION,
        };
    }

    public function asignacionEquipos(): HasMany
    {
        return $this->hasMany(\App\Models\AsignacionEquipo::class, 'equipo_id');
    }

    // =========================================================
    // HELPERS DE ÁREA
    // =========================================================

    /**
     * ¿El equipo pertenece actualmente al área de preparación?
     * Solo preparación puede editar estatus_area cuando esto es true.
     */
    public function esDePreparacion(): bool
    {
        return in_array($this->estatus_ciclo, [
            self::CICLO_CEDIS,
            self::CICLO_PREPARACION,
            self::CICLO_CALIDAD,
        ], true);
    }

    /**
     * ¿El equipo ya salió de preparación y no debe ser tocado por ese área?
     */
    public function salioDePrepacion(): bool
    {
        return in_array($this->estatus_ciclo, [
            self::CICLO_VENTAS,
            self::CICLO_APARTADO,
            self::CICLO_VENDIDO,
            self::CICLO_SCRAP,
        ], true);
    }

    /**
     * ¿El equipo está en un estado final (no puede avanzar más)?
     */
    public function esEstadoFinal(): bool
    {
        return in_array($this->estatus_ciclo, [
            self::CICLO_VENDIDO,
            self::CICLO_SCRAP,
        ], true);
    }

    /**
     * ¿El equipo está bloqueado esperando algo?
     */
    public function estaBloqueado(): bool
    {
        return in_array($this->estatus_area, [
            self::AREA_PENDIENTE_PIEZA,
            self::AREA_PENDIENTE_GARANTIA,
            self::AREA_PENDIENTE_DESARME,
            self::AREA_GARANTIA_INT,
            self::AREA_GARANTIA_EXT,
        ], true);
    }

    // =========================================================
    // LABELS para UI (blade/livewire)
    // =========================================================

    public static function labelsCiclo(): array
    {
        return [
            self::CICLO_CEDIS       => 'CEDIS',
            self::CICLO_PREPARACION => 'En Preparación',
            self::CICLO_CALIDAD     => 'En Calidad',
            self::CICLO_VENTAS      => 'En Ventas',
            self::CICLO_APARTADO    => 'Apartado',
            self::CICLO_VENDIDO     => 'Vendido',
            self::CICLO_SCRAP       => 'Scrap / Baja',
        ];
    }

    public static function labelsArea(): array
    {
        return [
            self::AREA_EN_ESPERA          => 'En espera',
            self::AREA_SIN_ASIGNAR        => 'Sin asignar',
            self::AREA_ASIGNADO           => 'Asignado',
            self::AREA_EN_PROCESO         => 'En proceso',
            self::AREA_EN_CALIDAD         => 'En calidad',
            self::AREA_FINALIZADO         => 'Finalizado',
            self::AREA_TRANSFERIDO        => 'Transferido',
            self::AREA_PENDIENTE_PIEZA    => 'Pendiente pieza',
            self::AREA_PENDIENTE_GARANTIA => 'Pendiente garantía',
            self::AREA_PENDIENTE_DESARME  => 'Pendiente desarme',
            self::AREA_GARANTIA_INT       => 'Garantía interna',
            self::AREA_GARANTIA_EXT       => 'Garantía externa',
        ];
    }

    // =========================================================
    // RELACIONES
    // =========================================================

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id')->withoutGlobalScopes();
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function loteModelo()
    {
        return $this->belongsTo(LoteModeloRecibido::class, 'lote_modelo_id');
    }

    /**
     * Tipo A-F para el sistema de puntos de preparación.
     * Diferente a tipo_equipo (que es texto libre "Laptop", "Desktop", etc.)
     */
    public function tipoEquipoPuntos()
    {
        return $this->belongsTo(\App\Models\TipoEquipo::class, 'tipo_equipo_id');
    }

    public function baterias()
    {
        return $this->hasMany(\App\Models\EquipoBateria::class, 'equipo_id');
    }

    public function monitor()
    {
        return $this->hasOne(\App\Models\EquipoMonitor::class);
    }

    public function movimientos()
    {
        return $this->hasMany(\App\Models\EquipoMovimiento::class)
            ->orderByDesc('created_at');
    }

    public function estancias()
    {
        return $this->hasMany(\App\Models\EquipoEstancia::class)
            ->orderByDesc('inicio_at');
    }

    public function estanciaActual()
    {
        return $this->hasOne(\App\Models\EquipoEstancia::class)
            ->whereNull('fin_at');
    }

    public function gpus()
    {
        return $this->hasMany(\App\Models\EquipoGpu::class, 'equipo_id');
    }

    public function gpuIntegrada()
    {
        return $this->hasOne(\App\Models\EquipoGpu::class, 'equipo_id')
            ->where('tipo', 'integrada')
            ->where('activo', true);
    }

    public function gpuDedicada()
    {
        return $this->hasOne(\App\Models\EquipoGpu::class, 'equipo_id')
            ->where('tipo', 'dedicada')
            ->where('activo', true);
    }

    public function clasificacionPuntos()
    {
        return $this->belongsTo(\App\Models\ClasificacionPuntos::class, 'clasificacion_puntos_id');
    }

    // =========================================================
    // CASTS
    // =========================================================

    protected $casts = [
        'puertos_usb'    => 'array',
        'puertos_video'  => 'array',
        'lectores'       => 'array',
        'ram_es_soldada' => 'boolean',
        'ram_sin_slots'  => 'boolean',
    ];
}
