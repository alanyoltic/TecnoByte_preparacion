<?php

namespace App\Livewire\Preparacion\Equipos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Asignaciones'])]
class Asignaciones extends Component
{
    // ── Vista activa ──────────────────────────────────────────────────────
    // 'panel' | 'nueva' | 'detalle'
    public string $vista = 'panel';

    // ── Detalle de técnico ────────────────────────────────────────────────
    public ?int $tecnicoSeleccionadoId = null;

    // ── Búsqueda técnico ──────────────────────────────────────────────────
    public string $busquedaTecnico = '';

    // ── Búsqueda lote ─────────────────────────────────────────────────────
    public string $busquedaLote = '';

    // ── Cancelar asignación ───────────────────────────────────────────────
    public ?int $cancelarAsignacionId = null;
    public string $motivoCancelacion = '';
    public bool $modalCancelar = false;

    // ── Nueva asignación: selecciones ─────────────────────────────────────
    public ?int $tecnicoId = null;
    public array $seleccion = [];
    // seleccion = [ lote_modelo_id => cantidad, ... ]

    public string $notas = '';
    public string $error = '';

    public function mount(): void
    {
        $this->autorizarGestionAsignaciones();
    }

    private function autorizarGestionAsignaciones(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.gestion'), 403);
    }

    // ── Computed: todas las asignaciones activas ──────────────────────────
    #[Computed]
    public function asignacionesActivas()
    {
        return Asignacion::with([
                'tecnico',
                'loteModelo.lote',
                'equipos',
            ])
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->orderBy('fecha_asignacion', 'desc')
            ->get()
            ->groupBy('tecnico_id');
    }

    // ── Computed: técnicos disponibles ────────────────────────────────────
    #[Computed]
    public function tecnicos()
    {
        return User::whereHas('role', fn($q) => $q->whereIn('slug', ['tecnico', 'lider']))
            ->where('is_active', true)
            ->when($this->busquedaTecnico, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('nombre', 'like', "%{$this->busquedaTecnico}%")
                       ->orWhere('apellido_paterno', 'like', "%{$this->busquedaTecnico}%")
                ))
            ->orderBy('nombre')
            ->get();
    }

    // ── Computed: lotes con modelos disponibles ───────────────────────────
    #[Computed]
    public function lotesDisponibles()
    {
        return Lote::with(['modelosRecibidos' => function($q) {
                $q->addSelect([
                    'lote_modelos_recibidos.*',
                    // Equipos ya escaneados/registrados físicamente
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM equipos
                        WHERE equipos.lote_modelo_id = lote_modelos_recibidos.id
                        AND equipos.deleted_at IS NULL
                    ) as equipos_registrados"),
                    // Slots reservados por asignaciones activas aún no escaneados
                    DB::raw("(
                        SELECT COALESCE(SUM(
                            GREATEST(a.cantidad - (
                                SELECT COUNT(*) FROM asignacion_equipos ae WHERE ae.asignacion_id = a.id
                            ), 0)
                        ), 0)
                        FROM asignaciones a
                        WHERE a.lote_modelo_id = lote_modelos_recibidos.id
                        AND a.estatus IN ('PENDIENTE','EN_PROCESO')
                        AND a.deleted_at IS NULL
                    ) as slots_reservados"),
                ]);
            }])
            ->whereHas('modelosRecibidos', fn($q) =>
                $q->whereColumn('cantidad_recibida', '>', DB::raw('0'))
            )
            ->when($this->busquedaLote, fn($q) =>
                $q->where('nombre_lote', 'like', "%{$this->busquedaLote}%")
            )
            ->orderByDesc('fecha_llegada')
            ->get()
            ->map(function($lote) {
                $lote->modelosRecibidos = $lote->modelosRecibidos->map(function($modelo) {
                    $modelo->equipos_libres = max(
                        (int)$modelo->cantidad_recibida
                            - (int)($modelo->equipos_registrados ?? 0)
                            - (int)($modelo->slots_reservados ?? 0),
                        0
                    );
                    return $modelo;
                })->values();
                return $lote;
            })
            ->filter(fn($lote) => $lote->modelosRecibidos->count() > 0)
            // Lotes con disponibles primero, agotados al final
            ->sortByDesc(fn($lote) => $lote->modelosRecibidos->sum('equipos_libres'))
            ->values();
    }

    #[Computed]
    public function metricas()
    {
        $asigs = Asignacion::with('equipos')
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->get();

        return [
            'tecnicos_activos'   => $asigs->pluck('tecnico_id')->unique()->count(),
            'equipos_asignados'  => $asigs->sum('cantidad'),
            'completados_hoy'    => AsignacionEquipo::where('camino', 'COMPLETADO')
                                        ->whereDate('fin_en', today())
                                        ->count(),
            'piezas_pendientes'  => AsignacionEquipo::where('camino', 'PIEZA_PENDIENTE')->count(),
            'garantias'          => AsignacionEquipo::whereIn('camino', ['GARANTIA_INTERNA','GARANTIA_EXTERNA'])->count(),
            'en_proceso'         => $asigs->sum(fn($a) => $a->equipos->where('camino','EN_PROCESO')->count()),
            'sin_asignar'        => \App\Models\Equipo::whereIn('estatus_ciclo', ['CEDIS','PREPARACION'])
                                        ->whereIn('estatus_area', ['SIN_ASIGNAR','EN_ESPERA'])
                                        ->whereNull('deleted_at')
                                        ->count(),
        ];
    }

    // ── Computed: detalle del técnico seleccionado ────────────────────────
    #[Computed]
    public function tecnicoDetalle(): ?User
    {
        if (!$this->tecnicoSeleccionadoId) return null;
        return User::find($this->tecnicoSeleccionadoId);
    }

    #[Computed]
    public function asignacionesTecnico()
    {
        if (!$this->tecnicoSeleccionadoId) return collect();
        return Asignacion::with(['loteModelo.lote', 'equipos'])
            ->where('tecnico_id', $this->tecnicoSeleccionadoId)
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->orderByDesc('fecha_asignacion')
            ->get();
    }

    // ── Navegar a detalle de técnico ──────────────────────────────────────
    public function verDetalle(int $tecnicoId): void
    {
        $this->tecnicoSeleccionadoId = $tecnicoId;
        $this->vista = 'detalle';
    }

    public function volverAPanel(): void
    {
        $this->vista = 'panel';
        $this->tecnicoSeleccionadoId = null;
        $this->reset(['error']);
    }

    public function irANuevaAsignacion(): void
    {
        $this->vista = 'nueva';
        $this->reset(['tecnicoId', 'seleccion', 'notas', 'error', 'busquedaTecnico', 'busquedaLote']);
    }

    public function volverDesdeNueva(): void
    {
        $this->vista = 'panel';
        $this->reset(['tecnicoId', 'seleccion', 'notas', 'error', 'busquedaTecnico', 'busquedaLote']);
    }

    // ── Seleccionar/deseleccionar técnico ─────────────────────────────────
    public function seleccionarTecnico(int $id): void
    {
        $this->tecnicoId = $id;
        $this->error = '';
    }

    // ── Actualizar cantidad de un modelo ──────────────────────────────────
    public function actualizarCantidad(int $loteModeloId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            unset($this->seleccion[$loteModeloId]);
        } else {
            $this->seleccion[$loteModeloId] = $cantidad;
        }
    }

    // ── Guardar asignación ────────────────────────────────────────────────
    public function guardarAsignacion(): void
    {
        $this->autorizarGestionAsignaciones();

        $this->error = '';

        if (!$this->tecnicoId) {
            $this->error = 'Debes seleccionar un técnico.';
            return;
        }

        if (empty($this->seleccion)) {
            $this->error = 'Debes seleccionar al menos un modelo con cantidad mayor a 0.';
            return;
        }

        // Validar que ninguna cantidad supere los disponibles
        // Descuenta: equipos ya registrados + slots reservados por asignaciones activas
        foreach ($this->seleccion as $loteModeloId => $cantidad) {
            $modelo      = \App\Models\LoteModeloRecibido::find($loteModeloId);
            $registrados = \App\Models\Equipo::where('lote_modelo_id', $loteModeloId)->whereNull('deleted_at')->count();
            $reservados  = Asignacion::where('lote_modelo_id', $loteModeloId)
                ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
                ->get()
                ->sum(fn($a) => max($a->cantidad - $a->equipos()->count(), 0));
            $disponibles = max((int)($modelo->cantidad_recibida ?? 0) - $registrados - $reservados, 0);

            if ($cantidad > $disponibles) {
                $this->error = "La cantidad para {$modelo->marca} {$modelo->modelo} supera los disponibles ({$disponibles}).";
                return;
            }
        }

        foreach ($this->seleccion as $loteModeloId => $cantidad) {
            Asignacion::create([
                'tecnico_id'      => $this->tecnicoId,
                'asignado_por_id' => Auth::id(),
                'lote_modelo_id'  => $loteModeloId,
                'cantidad'        => $cantidad,
                'fecha_asignacion'=> Carbon::today(),
                'estatus'         => Asignacion::PENDIENTE,
                'notas'           => $this->notas ?: null,
            ]);
        }

        $this->dispatch('toast', [
            'type'    => 'success',
            'title'   => 'Asignación creada',
            'message' => 'Los equipos han sido asignados correctamente.',
        ]);

        $this->volverDesdeNueva();
    }

    // ── Abrir modal cancelar ─────────────────────────────────────────────
    public function abrirModalCancelar(int $asignacionId): void
    {
        $this->autorizarGestionAsignaciones();

        $asignacion = Asignacion::with('equipos')->find($asignacionId);

        if (!$asignacion) return;

        // Verificar que no tenga equipos escaneados
        if ($asignacion->equipos->count() > 0) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'title'   => 'No se puede cancelar',
                'message' => 'Esta asignación ya tiene equipos iniciados.',
            ]);
            return;
        }

        $this->cancelarAsignacionId = $asignacionId;
        $this->motivoCancelacion = '';
        $this->modalCancelar = true;
    }

    public function cerrarModalCancelar(): void
    {
        $this->modalCancelar = false;
        $this->cancelarAsignacionId = null;
        $this->motivoCancelacion = '';
    }

    public function confirmarCancelacion(): void
    {
        $this->autorizarGestionAsignaciones();

        if (empty(trim($this->motivoCancelacion))) {
            $this->addError('motivoCancelacion', 'El motivo es obligatorio.');
            return;
        }

        $asignacion = Asignacion::with('equipos')->find($this->cancelarAsignacionId);

        if (!$asignacion) return;

        // Doble verificación
        if ($asignacion->equipos->count() > 0) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'title'   => 'No se puede cancelar',
                'message' => 'Esta asignación ya tiene equipos iniciados.',
            ]);
            $this->cerrarModalCancelar();
            return;
        }

        $asignacion->update([
            'estatus'        => Asignacion::CANCELADO,
            'notas_entrega'  => 'CANCELADA — Motivo: ' . trim($this->motivoCancelacion),
        ]);

        $this->dispatch('toast', [
            'type'    => 'success',
            'title'   => 'Asignación cancelada',
            'message' => 'La asignación fue cancelada correctamente.',
        ]);

        $this->cerrarModalCancelar();
        unset($this->asignacionesActivas);
    }

    public function render()
    {
        return view('livewire.preparacion.equipos.asignaciones');
    }
}
