<?php

namespace App\Livewire\Preparacion\Equipos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    // Series específicas opcionales: [ lote_modelo_id => [serie1, serie2, ...] ]
    public array $seriesAsignadas = [];

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

    private function queryEquiposSinAsignarPorModelo(int $loteModeloId): Builder
    {
        return \App\Models\Equipo::query()
            ->where('lote_modelo_id', $loteModeloId)
            ->whereNull('deleted_at')
            ->where('estatus_area', \App\Models\Equipo::AREA_SIN_ASIGNAR);
    }

    private function queryEquiposConSerieDeLotePorModelo(int $loteModeloId): Builder
    {
        return $this->queryEquiposSinAsignarPorModelo($loteModeloId)
            ->whereNotNull('numero_serie')
            ->whereRaw("TRIM(numero_serie) <> ''")
            ->whereExists(function ($q) {
                $q->select(DB::raw('1'))
                    ->from('equipo_movimientos as em')
                    ->whereColumn('em.equipo_id', 'equipos.id')
                    ->where('em.tipo', 'ALTA_LOTE');
            });
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
        // Técnicos + Líderes activos como técnicos + Líderes inactivos con equipos históricos
        return User::where(function($q) {
            // Técnicos (siempre)
            $q->whereHas('role', fn($r) => $r->where('slug', 'tecnico'))
              ->where('is_active', true);
        })
        ->orWhere(function($q) {
            // Líderes activos como técnicos
            $q->whereHas('role', fn($r) => $r->where('slug', 'lider'))
              ->where('is_active', true)
              ->whereHas('liderModoTecnico', fn($lmt) => $lmt->where('es_tecnico', true));
        })
        ->orWhere(function($q) {
            // Líderes inactivos o sin modo técnico, pero con asignaciones históricos
            $q->whereHas('role', fn($r) => $r->where('slug', 'lider'))
              ->where('is_active', true)
              ->whereHas('asignaciones'); // que tienen alguna asignación
        })
        ->when($this->busquedaTecnico, fn($q) =>
            $q->where(fn($q2) =>
                $q2->where('nombre', 'like', "%{$this->busquedaTecnico}%")
                   ->orWhere('apellido_paterno', 'like', "%{$this->busquedaTecnico}%")
            ))
        ->distinct()
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
                    // Equipos físicos listos para asignar de inmediato
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM equipos
                        WHERE equipos.lote_modelo_id = lote_modelos_recibidos.id
                        AND equipos.deleted_at IS NULL
                        AND equipos.estatus_area = 'SIN_ASIGNAR'
                        AND (
                            equipos.numero_serie IS NULL
                            OR TRIM(equipos.numero_serie) = ''
                        )
                    ) as equipos_sin_serie_disponibles"),
                    // Equipos con serie elegibles para selector (solo alta de lote)
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM equipos
                        WHERE equipos.lote_modelo_id = lote_modelos_recibidos.id
                        AND equipos.deleted_at IS NULL
                        AND equipos.estatus_area = 'SIN_ASIGNAR'
                        AND equipos.numero_serie IS NOT NULL
                        AND TRIM(equipos.numero_serie) <> ''
                        AND EXISTS (
                            SELECT 1
                            FROM equipo_movimientos em
                            WHERE em.equipo_id = equipos.id
                            AND em.tipo = 'ALTA_LOTE'
                        )
                    ) as equipos_con_serie_lote_disponibles"),
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
                    $disponiblesSinSerie = (int) ($modelo->equipos_sin_serie_disponibles ?? 0);
                    $disponiblesSerieLote = (int) ($modelo->equipos_con_serie_lote_disponibles ?? 0);
                    $cupoSinRegistrarLibre = max(
                        (int) $modelo->cantidad_recibida
                            - (int) ($modelo->equipos_registrados ?? 0)
                            - (int) ($modelo->slots_reservados ?? 0),
                        0
                    );

                    $modelo->equipos_libres = $disponiblesSinSerie + $disponiblesSerieLote + $cupoSinRegistrarLibre;
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
            'completados_hoy'    => AsignacionEquipo::whereIn('camino', ['COMPLETADO', 'EN_CALIDAD'])
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
        $this->reset(['tecnicoId', 'seleccion', 'seriesAsignadas', 'notas', 'error', 'busquedaTecnico', 'busquedaLote']);
        unset($this->equiposSinAsignarPorModelo);
    }

    public function volverDesdeNueva(): void
    {
        $this->vista = 'panel';
        $this->reset(['tecnicoId', 'seleccion', 'seriesAsignadas', 'notas', 'error', 'busquedaTecnico', 'busquedaLote']);
        unset($this->equiposSinAsignarPorModelo);
    }

    // ── Seleccionar/deseleccionar técnico ─────────────────────────────────
    public function seleccionarTecnico(int $id): void
    {
        $this->tecnicoId = $id;
        $this->error = '';
    }

    // ── Equipos SIN_ASIGNAR disponibles para pre-asignar (por modelo) ──────
    #[Computed]
    public function equiposSinAsignarPorModelo(): \Illuminate\Support\Collection
    {
        $ids = array_keys($this->seleccion ?: []);
        if (empty($ids)) return collect();

        return \App\Models\Equipo::whereIn('lote_modelo_id', $ids)
            ->where('estatus_area', \App\Models\Equipo::AREA_SIN_ASIGNAR)
            ->whereNotNull('numero_serie')
            ->whereRaw("TRIM(numero_serie) <> ''")
            ->whereExists(function ($q) {
                $q->select(DB::raw('1'))
                    ->from('equipo_movimientos as em')
                    ->whereColumn('em.equipo_id', 'equipos.id')
                    ->where('em.tipo', 'ALTA_LOTE');
            })
            ->get(['id', 'numero_serie', 'lote_modelo_id'])
            ->groupBy('lote_modelo_id');
    }

    // ── Actualizar cantidad de un modelo ──────────────────────────────────
    public function actualizarCantidad(int $loteModeloId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            unset($this->seleccion[$loteModeloId]);
            unset($this->seriesAsignadas[$loteModeloId]);
        } else {
            $this->seleccion[$loteModeloId] = $cantidad;
        }
        unset($this->equiposSinAsignarPorModelo);
    }

    // ── Toggle serie específica para un modelo ────────────────────────────
    public function toggleSerie(int $loteModeloId, string $serie): void
    {
        $actuales = $this->seriesAsignadas[$loteModeloId] ?? [];
        $cantidad = $this->seleccion[$loteModeloId] ?? 0;

        if (in_array($serie, $actuales, true)) {
            $this->seriesAsignadas[$loteModeloId] = array_values(array_diff($actuales, [$serie]));
        } elseif (count($actuales) < $cantidad) {
            $this->seriesAsignadas[$loteModeloId][] = $serie;
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

        // Validar que ninguna cantidad supere los disponibles reales:
        // equipos físicos SIN_ASIGNAR + cupo pendiente de registrar no reservado.
        foreach ($this->seleccion as $loteModeloId => $cantidad) {
            $modelo      = \App\Models\LoteModeloRecibido::find($loteModeloId);
            $registrados = \App\Models\Equipo::where('lote_modelo_id', $loteModeloId)->whereNull('deleted_at')->count();
            $sinSerieDisponibles = $this->queryEquiposSinAsignarPorModelo($loteModeloId)
                ->where(function ($q) {
                    $q->whereNull('numero_serie')
                        ->orWhereRaw("TRIM(numero_serie) = ''");
                })
                ->count();
            $conSerieLoteDisponibles = $this->queryEquiposConSerieDeLotePorModelo($loteModeloId)->count();
            $reservados  = Asignacion::where('lote_modelo_id', $loteModeloId)
                ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
                ->get()
                ->sum(fn($a) => max($a->cantidad - $a->equipos()->count(), 0));
            $cupoSinRegistrarLibre = max((int)($modelo->cantidad_recibida ?? 0) - $registrados - $reservados, 0);
            $disponibles = $sinSerieDisponibles + $conSerieLoteDisponibles + $cupoSinRegistrarLibre;
            $seriesElegidas = array_values(array_unique(array_filter(
                array_map('trim', $this->seriesAsignadas[$loteModeloId] ?? []),
                fn($s) => $s !== ''
            )));

            $minSeriesObligatorias = max($cantidad - ($sinSerieDisponibles + $cupoSinRegistrarLibre), 0);

            if ($cantidad > $disponibles) {
                $this->error = "La cantidad para {$modelo->marca} {$modelo->modelo} supera los disponibles ({$disponibles}).";
                return;
            }

            if (count($seriesElegidas) < $minSeriesObligatorias) {
                $faltan = $minSeriesObligatorias - count($seriesElegidas);
                $this->error = "Para {$modelo->marca} {$modelo->modelo} debes seleccionar {$faltan} serie(s) más. Ya no alcanzan los equipos sin serie.";
                return;
            }

            if (!empty($seriesElegidas)) {
                $seriesValidas = $this->queryEquiposConSerieDeLotePorModelo($loteModeloId)
                    ->whereIn('numero_serie', $seriesElegidas)
                    ->count();

                if ($seriesValidas !== count($seriesElegidas)) {
                    $this->error = "Algunas series seleccionadas para {$modelo->marca} {$modelo->modelo} ya no están disponibles. Recarga y vuelve a seleccionarlas.";
                    return;
                }
            }
        }

        DB::transaction(function () {
            foreach ($this->seleccion as $loteModeloId => $cantidad) {
                $asignacion = Asignacion::create([
                    'tecnico_id'      => $this->tecnicoId,
                    'asignado_por_id' => Auth::id(),
                    'lote_modelo_id'  => $loteModeloId,
                    'cantidad'        => $cantidad,
                    'fecha_asignacion'=> Carbon::today(),
                    'estatus'         => Asignacion::PENDIENTE,
                    'notas'           => $this->notas ?: null,
                ]);

                // Pre-asignar series con número de serie conocido
                $seriesElegidas = array_values(array_unique(array_filter(
                    array_map('trim', $this->seriesAsignadas[$loteModeloId] ?? []),
                    fn($s) => $s !== ''
                )));

                // Primero las series que el gerente eligió manualmente
                $asignadas = 0;
                if (!empty($seriesElegidas)) {
                    $equipos = $this->queryEquiposConSerieDeLotePorModelo($loteModeloId)
                        ->whereIn('numero_serie', $seriesElegidas)
                        ->get();

                    foreach ($equipos as $equipo) {
                        AsignacionEquipo::create([
                            'asignacion_id' => $asignacion->id,
                            'equipo_id'     => $equipo->id,
                            'camino'        => AsignacionEquipo::PRE_ASIGNADO,
                            'pre_asignado'  => true,
                        ]);
                        $equipo->update(['estatus_area' => \App\Models\Equipo::AREA_ASIGNADO]);
                        $asignadas++;
                    }
                }

                // Luego auto-asignar aleatoriamente los slots restantes
                $restantes = $cantidad - $asignadas;
                if ($restantes > 0) {
                    $equiposAuto = $this->queryEquiposSinAsignarPorModelo($loteModeloId)
                        ->where(function ($q) {
                            $q->whereNull('numero_serie')
                                ->orWhereRaw("TRIM(numero_serie) = ''");
                        })
                        ->inRandomOrder()
                        ->limit($restantes)
                        ->get();

                    foreach ($equiposAuto as $equipo) {
                        AsignacionEquipo::create([
                            'asignacion_id' => $asignacion->id,
                            'equipo_id'     => $equipo->id,
                            'camino'        => AsignacionEquipo::PRE_ASIGNADO,
                            'pre_asignado'  => true,
                        ]);
                        $equipo->update(['estatus_area' => \App\Models\Equipo::AREA_ASIGNADO]);
                    }
                }
            }
        });

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

        // Solo cancelable si no hay equipos ya iniciados (EN_PROCESO o más)
        $iniciados = $asignacion->equipos->filter(
            fn($ae) => !in_array($ae->camino, [AsignacionEquipo::PENDIENTE, AsignacionEquipo::PRE_ASIGNADO])
        )->count();

        if ($iniciados > 0) {
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

        $asignacion = Asignacion::with('equipos.equipo')->find($this->cancelarAsignacionId);

        if (!$asignacion) return;

        $iniciados = $asignacion->equipos->filter(
            fn($ae) => !in_array($ae->camino, [AsignacionEquipo::PENDIENTE, AsignacionEquipo::PRE_ASIGNADO], true)
        )->count();

        if ($iniciados > 0) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'title'   => 'No se puede cancelar',
                'message' => 'Esta asignación ya tiene equipos iniciados.',
            ]);
            $this->cerrarModalCancelar();
            return;
        }

        DB::transaction(function () use ($asignacion) {
            // Liberar equipos aún no iniciados (PENDIENTE / PRE_ASIGNADO) a SIN_ASIGNAR
            foreach ($asignacion->equipos->filter(
                fn($ae) => in_array($ae->camino, [AsignacionEquipo::PENDIENTE, AsignacionEquipo::PRE_ASIGNADO], true)
            ) as $ae) {
                $ae->equipo?->update(['estatus_area' => \App\Models\Equipo::AREA_SIN_ASIGNAR]);
                $ae->delete();
            }

            $asignacion->update([
                'estatus'       => Asignacion::CANCELADO,
                'notas_entrega' => 'CANCELADA — Motivo: ' . trim($this->motivoCancelacion),
            ]);
        });

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
