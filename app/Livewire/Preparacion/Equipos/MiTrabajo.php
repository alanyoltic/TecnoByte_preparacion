<?php

namespace App\Livewire\Preparacion\Equipos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Livewire\Preparacion\Forms\EquipoForm;
use App\Models\{
    Asignacion, AsignacionEquipo, Equipo,
    SolicitudPieza, PuntoTecnico,
    EquipoBateria, EquipoMonitor, EquipoGpu,
    CatalogoPieza
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Mi Trabajo'])]
class MiTrabajo extends Component
{
    // ── Vista activa ──────────────────────────────────────────────────────
    // 'lista' | 'equipos' | 'caracteristicas' | 'terminar' | 'confirmar_pieza'
    public string $vista = 'lista';

    // ── Navegación ────────────────────────────────────────────────────────
    public ?int $asignacionId       = null;
    public ?int $asignacionEquipoId = null;
    public ?int $solicitudPiezaId   = null;

    // ── Form Object (mismo que RegistrarEquipo) ───────────────────────────
    public EquipoForm $form;

    // ── Si el equipo ya está terminado (solo lectura) ─────────────────────
    public bool $equipoTerminado = false;

    // ── Fase 1: iniciar equipos (múltiples series) ────────────────────────
    public array  $series     = [''];
    public string $errorSerie = '';

    // ── Fase 3: terminar ──────────────────────────────────────────────────
    public string $camino                = 'COMPLETADO';
    public string $notasTerminar         = '';
    public ?int   $catalogoPiezaId       = null;  // pieza seleccionada del catálogo
    public int    $cantidadPieza         = 1;     // cantidad que necesita
    public string $busquedaPieza         = '';    // búsqueda en tiempo real
    public string $filtroCategoriaPieza  = '';    // filtro por categoría
    public string $descripcionPiezaLibre = '';    // notas / especificación adicional

    // ── Búsqueda de equipo por serie ─────────────────────────────────────
    public string $busquedaSerie = '';

    // ── Error general ─────────────────────────────────────────────────────
    public string $error = '';

    // ── Catálogos UI (igual que RegistrarEquipo) ──────────────────────────
    public array $monitorEntradasOptions = [
        'HDMI', 'Mini HDMI', 'VGA', 'DVI',
        'DisplayPort', 'Mini DisplayPort',
        'USB 2.0', 'USB 3.0', 'USB 3.1', 'USB 3.2', 'USB-C',
    ];

    // Maps UI -> columnas DB
    private const MAP_USB = [
        'USB 2.0' => 'puertos_usb_2', 'USB 3.0' => 'puertos_usb_30',
        'USB 3.1' => 'puertos_usb_31', 'USB 3.2' => 'puertos_usb_32',
        'USB-C' => 'puertos_usb_c', 'USB tipo C' => 'puertos_usb_c',
    ];
    private const MAP_VIDEO = [
        'HDMI' => 'puertos_hdmi', 'Mini HDMI' => 'puertos_mini_hdmi',
        'VGA' => 'puertos_vga', 'DVI' => 'puertos_dvi',
        'DisplayPort' => 'puertos_displayport', 'Mini DisplayPort' => 'puertos_mini_dp',
    ];
    private const MAP_LECTORES = [
        'SD' => 'lectores_sd', 'microSD' => 'lectores_microsd',
        'SmartCard' => 'lectores_sc', 'eSATA' => 'lectores_esata', 'SIM' => 'lectores_sim',
    ];
    private const MAP_SLOTS = [
        'SSD' => 'slots_alm_ssd', 'M.2' => 'slots_alm_m2',
        'M.2 MICRO' => 'slots_alm_m2_micro', 'HDD' => 'slots_alm_hdd', 'MSATA' => 'slots_alm_msata',
    ];
    private const MAP_MONITOR_IN = [
        'HDMI' => 'in_hdmi', 'Mini HDMI' => 'in_mini_hdmi',
        'VGA' => 'in_vga', 'DVI' => 'in_dvi',
        'DisplayPort' => 'in_displayport', 'Mini DisplayPort' => 'in_mini_displayport',
        'USB 2.0' => 'in_usb_2', 'USB 3.0' => 'in_usb_3',
        'USB 3.1' => 'in_usb_31', 'USB 3.2' => 'in_usb_32', 'USB-C' => 'in_usb_c',
    ];

    // ══════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ══════════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $this->autorizarTrabajo();
        $this->setDefaultsEnForm();
    }

    private function autorizarTrabajo(): void
    {
        $user = auth()->user();
        $roleSlug = strtolower((string) ($user?->role?->slug ?? ''));

        abort_unless(
            $user?->tienePermiso('prep.equipos.ver') && in_array($roleSlug, ['lider', 'tecnico'], true),
            403
        );
    }

    private function setDefaultsEnForm(): void
    {
        $f = $this->form;
        $f->estatus_area = 'EN_PROCESO';
        $f->almacenamiento_secundario_capacidad = $f->almacenamiento_secundario_capacidad ?: 'N/A';
        $f->almacenamiento_secundario_tipo      = $f->almacenamiento_secundario_tipo ?: 'N/A';
        $f->teclado_idioma                      = $f->teclado_idioma ?: 'N/A';
        $f->bateria_tiene  = $f->bateria_tiene ?? true;
        $f->bateria2_tiene = $f->bateria2_tiene ?? false;
        $f->ethernet_tiene      = (bool) ($f->ethernet_tiene ?? false);
        $f->ethernet_es_gigabit = (bool) ($f->ethernet_es_gigabit ?? false);
        $f->puertos_usb ??= [];
        $f->puertos_video ??= [];
        $f->lectores ??= [];
        $f->slots_almacenamiento ??= [];
        $f->monitor_entradas_rows ??= [];
        $f->gpu_integrada_tiene = (bool) ($f->gpu_integrada_tiene ?? false);
        $f->gpu_dedicada_tiene  = (bool) ($f->gpu_dedicada_tiene ?? false);
        $f->gpu_integrada_vram_unidad = $f->gpu_integrada_vram_unidad ?: 'GB';
        $f->gpu_dedicada_vram_unidad  = $f->gpu_dedicada_vram_unidad ?: 'GB';
        $f->monitor_incluido = $f->monitor_incluido ?: 'NO';
    }

    // ══════════════════════════════════════════════════════════════════════
    // COMPUTED
    // ══════════════════════════════════════════════════════════════════════

    #[Computed]
    public function asignaciones()
    {
        return Asignacion::with(['loteModelo.lote', 'equipos'])
            ->delTecnico(Auth::id())
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->orderByDesc('fecha_asignacion')
            ->get();
    }

    #[Computed]
    public function estadisticasMes()
    {
        $periodo = now()->format('Y-m');
        $puntos  = PuntoTecnico::where('tecnico_id', Auth::id())
            ->where('periodo', $periodo)->get();
        return [
            'total'         => $puntos->count(),
            'calidad'       => $puntos->where('rol_en_equipo', PuntoTecnico::COMPLETO)->count(),
            'piezas'        => $puntos->where('rol_en_equipo', PuntoTecnico::INICIO_PIEZA)->count(),
            'termino_pieza' => $puntos->where('rol_en_equipo', PuntoTecnico::TERMINO_PIEZA)->count(),
            'garantia'      => $puntos->where('rol_en_equipo', PuntoTecnico::GARANTIA)->count(),
            'puntos'        => round($puntos->sum('puntos_final'), 2),
        ];
    }

    #[Computed]
    public function asignacionActual(): ?Asignacion
    {
        if (!$this->asignacionId) return null;
        return Asignacion::with(['loteModelo.lote', 'equipos.equipo'])->find($this->asignacionId);
    }

    #[Computed]
    public function solicitudesPieza()
    {
        return SolicitudPieza::where('reasignado_a_id', Auth::id())
            ->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)
            ->with(['equipo', 'catalogoPieza', 'inventarioPieza.almacen'])
            ->orderBy('reasignada_en', 'desc')
            ->get();
    }

    #[Computed]
    public function solicitudPiezaActual(): ?SolicitudPieza
    {
        if (!$this->solicitudPiezaId) return null;
        return SolicitudPieza::with(['equipo', 'catalogoPieza', 'inventarioPieza.almacen'])->find($this->solicitudPiezaId);
    }

    #[Computed]
    public function catalogoPiezas()
    {
        return CatalogoPieza::activos()
            ->when($this->filtroCategoriaPieza, fn($q) =>
                $q->where('categoria', $this->filtroCategoriaPieza)
            )
            ->when($this->busquedaPieza, fn($q) =>
                $q->where('nombre', 'like', '%' . $this->busquedaPieza . '%')
            )
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'especificacion']);
    }

    #[Computed]
    public function categoriasDisponibles()
    {
        return CatalogoPieza::activos()
            ->select('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');
    }

    #[Computed]
    public function equipoActual(): ?AsignacionEquipo
    {
        if (!$this->asignacionEquipoId) return null;
        return AsignacionEquipo::with('equipo')->find($this->asignacionEquipoId);
    }

    public function getPantallaIntegradaProperty(): bool
    {
        return $this->isLaptopLikeTipo($this->form->tipo_equipo ?? null);
    }

    public function getPantallaExternaProperty(): bool
    {
        return $this->isPcLikeTipo($this->form->tipo_equipo ?? null);
    }

    // ══════════════════════════════════════════════════════════════════════
    // NAVEGACIÓN
    // ══════════════════════════════════════════════════════════════════════

    public function verEquipos(int $asignacionId): void
    {
        $this->autorizarTrabajo();

        $this->asignacionId = $asignacionId;
        $this->series       = [''];
        $this->errorSerie   = '';
        $this->error        = '';
        $this->vista        = 'equipos';
        unset($this->asignacionActual);
    }

    public function verCaracteristicas(int $asignacionEquipoId): void
    {
        $this->autorizarTrabajo();

        $ae     = AsignacionEquipo::with('equipo')->find($asignacionEquipoId);
        $equipo = $ae?->equipo;
        if (!$equipo) return;

        $this->asignacionEquipoId = $asignacionEquipoId;
        $this->equipoTerminado    = $ae->camino !== AsignacionEquipo::EN_PROCESO;

        // Cargar datos del equipo en el form
        $this->form->fillFromModel($equipo);

        // Pre-llenar campos de solo lectura
        $this->form->numero_serie   = $equipo->numero_serie;
        $this->form->marca          = $equipo->marca;
        $this->form->modelo         = $equipo->modelo;
        $this->form->lote_modelo_id = $equipo->lote_modelo_id;

        // Cargar GPU desde equipo_gpus
        $gpuInt = EquipoGpu::where('equipo_id', $equipo->id)->where('tipo', 'INTEGRADA')->first();
        $gpuDed = EquipoGpu::where('equipo_id', $equipo->id)->where('tipo', 'DEDICADA')->first();

        $this->form->gpu_integrada_tiene  = (bool) $gpuInt;
        $this->form->gpu_integrada_marca  = $gpuInt?->marca;
        $this->form->gpu_integrada_modelo = $gpuInt?->modelo;
        $this->form->gpu_integrada_vram   = $gpuInt?->vram;
        $this->form->gpu_integrada_vram_unidad = $gpuInt?->vram_unidad ?: 'GB';

        $this->form->gpu_dedicada_tiene  = (bool) $gpuDed;
        $this->form->gpu_dedicada_marca  = $gpuDed?->marca;
        $this->form->gpu_dedicada_modelo = $gpuDed?->modelo;
        $this->form->gpu_dedicada_vram   = $gpuDed?->vram;
        $this->form->gpu_dedicada_vram_unidad = $gpuDed?->vram_unidad ?: 'GB';

        // Cargar batería
        $baterias = EquipoBateria::where('equipo_id', $equipo->id)->get();
        $bat1 = $baterias->first();
        $bat2 = $baterias->skip(1)->first();
        $this->form->bateria_tiene  = $bat1 ? true : (bool) $equipo->bateria_tiene;
        $this->form->bateria1_tipo  = $bat1?->tipo;
        $this->form->bateria1_salud = $bat1?->salud_percent;
        $this->form->bateria2_tiene = (bool) $bat2;
        $this->form->bateria2_tipo  = $bat2?->tipo;
        $this->form->bateria2_salud = $bat2?->salud_percent;

        // Cargar monitor
        $monitor = EquipoMonitor::where('equipo_id', $equipo->id)->first();
        if ($monitor) {
            $this->form->monitor_incluido   = $monitor->incluido ? 'SI' : 'NO';
            $this->form->monitor_pulgadas   = $monitor->pulgadas;
            $this->form->monitor_resolucion = $monitor->resolucion;
            $this->form->pantalla_pulgadas  = $monitor->pulgadas;
            $this->form->pantalla_resolucion = $monitor->resolucion;
            $this->form->pantalla_es_touch  = (bool) $monitor->es_touch;
        }

        $this->setDefaultsEnForm();
        $this->error = '';
        $this->vista = 'caracteristicas';
        unset($this->equipoActual);
    }

    public function verTerminar(int $asignacionEquipoId): void
    {
        $this->autorizarTrabajo();

        $this->asignacionEquipoId    = $asignacionEquipoId;
        $this->camino                = 'COMPLETADO';
        $this->notasTerminar         = '';
        $this->catalogoPiezaId       = null;
        $this->cantidadPieza         = 1;
        $this->busquedaPieza         = '';
        $this->filtroCategoriaPieza  = '';
        $this->descripcionPiezaLibre = '';
        $this->error                 = '';
        $this->vista                 = 'terminar';
    }

    public function volverALista(): void
    {
        $this->vista              = 'lista';
        $this->asignacionId       = null;
        $this->asignacionEquipoId = null;
        $this->error              = '';
        $this->busquedaSerie      = '';
        unset($this->asignaciones, $this->asignacionActual);
    }

    public function volverAEquipos(): void
    {
        $this->vista              = 'equipos';
        $this->asignacionEquipoId = null;
        $this->error              = '';
        unset($this->asignacionActual, $this->asignaciones);
    }

    public function volverACaracteristicas(): void
    {
        $this->vista = 'caracteristicas';
        $this->error = '';
    }

    public function verConfirmarPieza(int $solicitudId): void
    {
        $this->autorizarTrabajo();

        $solicitud = SolicitudPieza::where('id', $solicitudId)
            ->where('reasignado_a_id', Auth::id())
            ->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)
            ->first();

        if (!$solicitud) return;

        $this->solicitudPiezaId = $solicitudId;
        $this->error = '';
        $this->vista = 'confirmar_pieza';
        unset($this->solicitudPiezaActual, $this->solicitudesPieza);
    }

    public function iniciarInstalacionPieza(): void
    {
        $this->autorizarTrabajo();

        $solicitud = SolicitudPieza::where('id', $this->solicitudPiezaId)
            ->where('reasignado_a_id', Auth::id())
            ->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)
            ->whereNull('iniciada_instalacion_en')
            ->first();

        if (!$solicitud) return;

        $solicitud->update(['iniciada_instalacion_en' => now()]);

        // Volver a la lista para que el técnico pueda trabajar en otros equipos
        $this->solicitudPiezaId = null;
        $this->vista = 'lista';
        unset($this->solicitudPiezaActual, $this->solicitudesPieza);
        $this->dispatch('toast', type: 'success', message: 'Instalación iniciada. Cuando termines, vuelve aquí para confirmar el resultado.');
    }

    public function confirmarInstalacionPieza(bool $funciono): void
    {
        $this->autorizarTrabajo();

        $solicitud = SolicitudPieza::with(['equipo', 'asignacionEquipo'])
            ->where('id', $this->solicitudPiezaId)
            ->where('reasignado_a_id', Auth::id())
            ->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)
            ->first();

        if (!$solicitud) return;

        if (!$solicitud->fueIniciada()) {
            $this->dispatch('toast', type: 'error', message: 'Debes iniciar la instalación antes de confirmar.');
            return;
        }

        try {
            DB::transaction(function () use ($solicitud, $funciono) {
                // 1. Confirmar instalación (actualiza pieza e inventario)
                $solicitud->confirmarInstalacion($funciono, null);

                $equipo = $solicitud->equipo;

                // 2. Actualizar estatus del equipo según resultado
                if ($equipo && $funciono) {
                    // Pieza funcionó → el equipo queda listo para calidad
                    $equipo->update([
                        'estatus_ciclo' => 'CALIDAD',
                        'estatus_area'  => 'EN_CALIDAD',
                        'almacen_id'    => 5, // almacén Calidad
                    ]);
                } elseif ($equipo && !$funciono) {
                    // Pieza no funcionó → crear nueva solicitud automáticamente
                    // El equipo se queda en PENDIENTE_PIEZA hasta que llegue otra pieza
                    SolicitudPieza::create([
                        'asignacion_equipo_id' => $solicitud->asignacion_equipo_id,
                        'equipo_id'            => $solicitud->equipo_id,
                        'solicitado_por_id'    => Auth::id(),
                        'catalogo_pieza_id'    => $solicitud->catalogo_pieza_id,
                        'descripcion_libre'    => $solicitud->descripcion_libre
                                                    ?? 'Reintento — pieza anterior defectuosa',
                        'estatus'              => SolicitudPieza::PENDIENTE,
                    ]);
                }

                // 3. Registrar puntos de la mini-vida (solo si la pieza funcionó)
                if ($funciono && $solicitud->asignacion_equipo_id) {
                    $clasificacionId = $equipo?->clasificacion_puntos_id;
                    $puntosBase      = $clasificacionId
                        ? (\App\Models\ClasificacionPuntos::find($clasificacionId)?->puntos_base ?? 1.0)
                        : 1.0;

                    PuntoTecnico::registrar(
                        tecnicoId:         Auth::id(),
                        asignacionEquipoId: $solicitud->asignacion_equipo_id,
                        rol:               PuntoTecnico::TERMINO_PIEZA,
                        puntosBase:        (float) $puntosBase,
                        clasificacionId:   $clasificacionId,
                    );
                }
            });

            $mensaje = $funciono
                ? 'Pieza instalada. El equipo pasó a Calidad y se registraron tus puntos.'
                : 'La pieza no funcionó. Se generó una nueva solicitud al encargado automáticamente.';

            $this->dispatch('toast', type: 'success', message: $mensaje);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }

        $this->solicitudPiezaId = null;
        $this->vista = 'lista';
        unset($this->solicitudesPieza, $this->solicitudPiezaActual);
    }

    public function volverDesdePieza(): void
    {
        $this->solicitudPiezaId = null;
        $this->vista = 'lista';
        unset($this->solicitudesPieza, $this->solicitudPiezaActual);
    }

    // ══════════════════════════════════════════════════════════════════════
    // FASE 1: INICIAR EQUIPOS
    // ══════════════════════════════════════════════════════════════════════

    public function agregarSerie(): void { $this->series[] = ''; }

    public function quitarSerie(int $index): void
    {
        if (count($this->series) > 1) {
            array_splice($this->series, $index, 1);
            $this->series = array_values($this->series);
        }
    }

    public function iniciarEquipos(): void
    {
        $this->autorizarTrabajo();

        $this->errorSerie = '';
        $asignacion = $this->asignacionActual;

        $seriesLimpias = array_values(array_filter(
            array_map('trim', $this->series), fn($s) => $s !== ''
        ));

        if (empty($seriesLimpias)) {
            $this->errorSerie = 'Debes ingresar al menos un número de serie.';
            return;
        }

        $yaEscaneados = $asignacion->equipos->count();
        $disponibles  = $asignacion->cantidad - $yaEscaneados;

        if (count($seriesLimpias) > $disponibles) {
            $this->errorSerie = "Solo puedes iniciar {$disponibles} equipo(s) más en esta asignación.";
            return;
        }

        if (count($seriesLimpias) !== count(array_unique($seriesLimpias))) {
            $this->errorSerie = 'Hay números de serie duplicados en la lista.';
            return;
        }

        $loteModelo = $asignacion->loteModelo()->with('lote')->first();
        $errores    = [];

        foreach ($seriesLimpias as $serie) {
            $yaEnEsta = AsignacionEquipo::where('asignacion_id', $asignacion->id)
                ->whereHas('equipo', fn($q) => $q->where('numero_serie', $serie))
                ->exists();

            if ($yaEnEsta) { $errores[] = "{$serie}: ya fue escaneado."; continue; }

            $equipo = Equipo::where('numero_serie', $serie)->first();

            if ($equipo) {
                if ($equipo->lote_modelo_id !== $asignacion->lote_modelo_id) {
                    $errores[] = "{$serie}: pertenece a un modelo diferente."; continue;
                }
                $enOtra = AsignacionEquipo::where('equipo_id', $equipo->id)
                    ->where('camino', AsignacionEquipo::EN_PROCESO)
                    ->whereHas('asignacion', fn($q) =>
                        $q->where('id', '!=', $asignacion->id)
                          ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
                    )->exists();
                if ($enOtra) { $errores[] = "{$serie}: en uso por otro técnico."; continue; }

                // Si estaba pre-registrado (EN_ESPERA), activarlo
                if ($equipo->estatus_area === Equipo::AREA_EN_ESPERA) {
                    $equipo->update([
                        'estatus_ciclo'          => 'PREPARACION',
                        'estatus_area'           => 'EN_PROCESO',
                        'registrado_por_user_id' => Auth::id(),
                    ]);
                }
            } else {
                $equipo = Equipo::create([
                    'numero_serie'           => $serie,
                    'lote_modelo_id'         => $asignacion->lote_modelo_id,
                    'marca'                  => $loteModelo->marca ?? null,
                    'modelo'                 => $loteModelo->modelo ?? '',
                    'estatus_ciclo'          => 'PREPARACION',
                    'estatus_area'           => 'EN_PROCESO',
                    'registrado_por_user_id' => Auth::id(),
                    'proveedor_id'           => $loteModelo->lote->proveedor_id ?? null,
                    'almacen_id'             => 2,
                    'sucursal_id'            => Auth::user()->sucursal_id ?? 1,
                ]);
            }

            AsignacionEquipo::create([
                'asignacion_id' => $asignacion->id,
                'equipo_id'     => $equipo->id,
                'inicio_en'     => now(),
                'camino'        => AsignacionEquipo::EN_PROCESO,
            ]);
        }

        $asignacion->update(['estatus' => Asignacion::EN_PROCESO]);

        if (!empty($errores)) {
            $this->errorSerie = implode(' | ', $errores);
        } else {
            $this->series = [''];
            $this->dispatch('toast', type: 'success', message: count($seriesLimpias) . ' equipo(s) iniciado(s).');
        }

        unset($this->asignacionActual);
    }

    // ── Equipos pre-registrados (EN_ESPERA) del lote actual ───────────────

    #[Computed]
    public function equiposPreRegistrados(): \Illuminate\Database\Eloquent\Collection
    {
        $asignacion = $this->asignacionActual;
        if (!$asignacion) return collect();

        return Equipo::where('lote_modelo_id', $asignacion->lote_modelo_id)
            ->where('estatus_area', Equipo::AREA_EN_ESPERA)
            ->whereDoesntHave('asignacionEquipos', fn ($q) =>
                $q->where('camino', AsignacionEquipo::EN_PROCESO)
            )
            ->get(['id', 'numero_serie', 'marca', 'modelo']);
    }

    public function iniciarEquipoPreRegistrado(int $equipoId): void
    {
        $this->autorizarTrabajo();
        $this->errorSerie = '';

        $asignacion = $this->asignacionActual;
        $equipo     = Equipo::findOrFail($equipoId);

        if ($equipo->lote_modelo_id !== $asignacion->lote_modelo_id) {
            $this->errorSerie = 'Este equipo no pertenece al modelo de esta asignación.';
            return;
        }
        if ($asignacion->equipos->count() >= $asignacion->cantidad) {
            $this->errorSerie = 'La asignación ya alcanzó su capacidad máxima.';
            return;
        }
        $yaEnEsta = AsignacionEquipo::where('asignacion_id', $asignacion->id)
            ->where('equipo_id', $equipoId)
            ->exists();
        if ($yaEnEsta) {
            $this->errorSerie = 'Este equipo ya fue iniciado en esta asignación.';
            return;
        }

        DB::transaction(function () use ($equipo, $asignacion) {
            $equipo->update([
                'estatus_ciclo'          => 'PREPARACION',
                'estatus_area'           => 'EN_PROCESO',
                'registrado_por_user_id' => Auth::id(),
            ]);
            AsignacionEquipo::create([
                'asignacion_id' => $asignacion->id,
                'equipo_id'     => $equipo->id,
                'inicio_en'     => now(),
                'camino'        => AsignacionEquipo::EN_PROCESO,
            ]);
            $asignacion->update(['estatus' => Asignacion::EN_PROCESO]);
        });

        $this->dispatch('toast', type: 'success', message: 'Equipo iniciado correctamente.');
        unset($this->asignacionActual, $this->equiposPreRegistrados);
    }

    // ══════════════════════════════════════════════════════════════════════
    // FASE 2: GUARDAR CARACTERÍSTICAS (BORRADOR)
    // ══════════════════════════════════════════════════════════════════════

    public function guardarAvance(): void
    {
        $this->autorizarTrabajo();

        $ae     = $this->equipoActual;
        $equipo = $ae?->equipo;
        if (!$equipo) return;

        try {
            $f = $this->form;

            // Pre-procesamiento igual que RegistrarEquipo
            $f->detalles_esteticos      = $this->buildChecksText($f->detalles_esteticos_checks ?? [], $f->detalles_esteticos_otro);
            $f->detalles_funcionamiento = $this->buildChecksText($f->detalles_funcionamiento_checks ?? [], $f->detalles_funcionamiento_otro);
            $f->puertos_conectividad    = $this->truncate($f->puertos_conectividad, 255);
            $f->dispositivos_entrada    = $this->truncate($f->dispositivos_entrada, 255);

            $this->mapSlotsToDbColumns();
            $this->applyAggregatesToEquipoColumns();

            DB::transaction(function () use ($equipo) {
                $f = $this->form;

                $equipo->update([
                    'tipo_equipo'             => $f->tipo_equipo ?: null,
                    'sistema_operativo'       => $f->sistema_operativo ?: null,
                    'area_tienda'             => $f->area_tienda ?: null,
                    'procesador_modelo'       => $f->procesador_modelo ?: null,
                    'procesador_frecuencia'   => $f->procesador_frecuencia ?: null,
                    'procesador_generacion'   => $f->procesador_generacion ?: null,
                    'procesador_nucleos'      => $f->procesador_nucleos ?: null,
                    'ram_total'               => $f->ram_total ?: null,
                    'ram_tipo'                => $f->ram_tipo ?: null,
                    'ram_es_soldada'          => (bool) $f->ram_es_soldada,
                    'ram_slots_totales'       => $f->ram_slots_totales ?: null,
                    'ram_expansion_max'       => $f->ram_expansion_max ?: null,
                    'ram_cantidad_soldada'    => $f->ram_cantidad_soldada ?: null,
                    'almacenamiento_principal_capacidad'  => $f->almacenamiento_principal_capacidad ?: null,
                    'almacenamiento_principal_tipo'       => $f->almacenamiento_principal_tipo ?: null,
                    'almacenamiento_secundario_capacidad' => $f->almacenamiento_secundario_capacidad ?: 'N/A',
                    'almacenamiento_secundario_tipo'      => $f->almacenamiento_secundario_tipo ?: 'N/A',
                    'slots_alm_ssd'      => $f->slots_alm_ssd,
                    'slots_alm_m2'       => $f->slots_alm_m2,
                    'slots_alm_m2_micro' => $f->slots_alm_m2_micro,
                    'slots_alm_hdd'      => $f->slots_alm_hdd,
                    'slots_alm_msata'    => $f->slots_alm_msata,
                    'ethernet_tiene'     => (bool) $f->ethernet_tiene,
                    'ethernet_es_gigabit'=> (bool) $f->ethernet_es_gigabit,
                    'puertos_conectividad' => $f->puertos_conectividad,
                    'dispositivos_entrada' => $f->dispositivos_entrada,
                    'puertos_hdmi'        => $f->puertos_hdmi,
                    'puertos_mini_hdmi'   => $f->puertos_mini_hdmi,
                    'puertos_vga'         => $f->puertos_vga,
                    'puertos_dvi'         => $f->puertos_dvi,
                    'puertos_displayport' => $f->puertos_displayport,
                    'puertos_mini_dp'     => $f->puertos_mini_dp,
                    'puertos_usb_2'       => $f->puertos_usb_2,
                    'puertos_usb_30'      => $f->puertos_usb_30,
                    'puertos_usb_31'      => $f->puertos_usb_31,
                    'puertos_usb_32'      => $f->puertos_usb_32,
                    'puertos_usb_c'       => $f->puertos_usb_c,
                    'lectores_sd'         => $f->lectores_sd,
                    'lectores_microsd'    => $f->lectores_microsd,
                    'lectores_sc'         => $f->lectores_sc,
                    'lectores_esata'      => $f->lectores_esata,
                    'lectores_sim'        => $f->lectores_sim,
                    'bateria_tiene'       => (bool) $f->bateria_tiene,
                    'teclado_idioma'      => $f->teclado_idioma ?: 'N/A',
                    'notas_generales'     => $f->notas_generales ?: null,
                    'detalles_esteticos'  => $f->detalles_esteticos ?: null,
                    'detalles_funcionamiento' => $f->detalles_funcionamiento ?: null,
                ]);

                $this->guardarBaterias($equipo->id);
                $this->guardarMonitor($equipo->id);
                $this->guardarGpus($equipo->id);
            });

            $this->dispatch('toast', type: 'success', title: 'Avance guardado', message: 'Los datos del equipo se guardaron.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Error al guardar', message: $e->getMessage());
            \Log::error('guardarAvance error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // FASE 3: TERMINAR EQUIPO
    // ══════════════════════════════════════════════════════════════════════

    public function terminarEquipo(): void
    {
        $this->autorizarTrabajo();

        $this->error = '';

        if ($this->camino === 'PIEZA_PENDIENTE') {
            if (!$this->catalogoPiezaId && empty(trim($this->descripcionPiezaLibre))) {
                $this->error = 'Selecciona o describe la pieza que falta.';
                return;
            }
        }

        $ae     = AsignacionEquipo::with(['asignacion', 'equipo'])->find($this->asignacionEquipoId);
        $equipo = $ae?->equipo;
        if (!$ae || !$equipo) return;

        [$estatusCiclo, $estatusArea, $almacenId] = match($this->camino) {
            'COMPLETADO'       => ['CALIDAD',     'EN_CALIDAD',          5], // almacén Calidad
            'PIEZA_PENDIENTE'  => ['PREPARACION', 'PENDIENTE_PIEZA',     7], // almacén Piezas Pend.
            'GARANTIA_INTERNA' => ['PREPARACION', 'PENDIENTE_GARANTIA',  3], // almacén Garantías Int.
            'GARANTIA_EXTERNA' => ['PREPARACION', 'PENDIENTE_GARANTIA',  4], // almacén Garantías Ext.
            default            => ['PREPARACION', 'EN_PROCESO',          2], // almacén Preparación
        };

        $ae->update(['fin_en' => now(), 'camino' => $this->camino, 'notas' => $this->notasTerminar ?: null]);
        $equipo->update([
            'estatus_ciclo' => $estatusCiclo,
            'estatus_area'  => $estatusArea,
            'almacen_id'    => $almacenId,
        ]);

        if ($this->camino === 'PIEZA_PENDIENTE') {
            SolicitudPieza::create([
                'asignacion_equipo_id' => $ae->id,
                'equipo_id'            => $ae->equipo_id,
                'solicitado_por_id'    => Auth::id(),
                'catalogo_pieza_id'    => $this->catalogoPiezaId ?: null,
                'descripcion_libre'    => trim($this->descripcionPiezaLibre) ?: null,
                'estatus'              => SolicitudPieza::PENDIENTE,
            ]);
        }

        $clasificacionId = $equipo->clasificacion_puntos_id;
        $puntosBase      = $clasificacionId
            ? (\App\Models\ClasificacionPuntos::find($clasificacionId)?->puntos_base ?? 1.0)
            : 1.0;

        $rol = match($this->camino) {
            'COMPLETADO'                           => PuntoTecnico::COMPLETO,
            'PIEZA_PENDIENTE'                      => PuntoTecnico::INICIO_PIEZA,
            'GARANTIA_INTERNA', 'GARANTIA_EXTERNA' => PuntoTecnico::GARANTIA,
            default                                => PuntoTecnico::COMPLETO,
        };

        PuntoTecnico::registrar(
            tecnicoId: Auth::id(), asignacionEquipoId: $ae->id,
            rol: $rol, puntosBase: (float) $puntosBase, clasificacionId: $clasificacionId,
        );

        $asignacion = $ae->asignacion;
        $terminados = AsignacionEquipo::where('asignacion_id', $asignacion->id)
            ->where('camino', '!=', AsignacionEquipo::EN_PROCESO)->count();

        if ($terminados >= $asignacion->cantidad) {
            $asignacion->update(['estatus' => Asignacion::ENTREGADO, 'fecha_entrega' => now()->toDateString()]);
        }

        $this->dispatch('toast', type: 'success', title: 'Equipo terminado', message: 'El equipo fue marcado como terminado.');
        unset($this->asignacionActual);
        $this->volverAEquipos();
    }

    // ══════════════════════════════════════════════════════════════════════
    // MÉTODOS DEL FORM (copiados de RegistrarEquipo)
    // ══════════════════════════════════════════════════════════════════════

    public function addPuertoUsb(): void           { $this->form->puertos_usb[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoUsb($i): void      { $this->unsetIndex($this->form->puertos_usb, $i); }
    public function addPuertoVideo(): void         { $this->form->puertos_video[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removePuertoVideo($i): void    { $this->unsetIndex($this->form->puertos_video, $i); }
    public function addLector(): void              { $this->form->lectores[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeLector($i): void         { $this->unsetIndex($this->form->lectores, $i); }
    public function addSlotAlmacenamiento(): void  { $this->form->slots_almacenamiento[] = ['tipo' => '', 'cantidad' => null]; }
    public function removeSlotAlmacenamiento($i): void { $this->unsetIndex($this->form->slots_almacenamiento, $i); }
    public function addMonitorEntrada(): void      { $this->form->monitor_entradas_rows[] = ['tipo' => '', 'cantidad' => 1]; }
    public function removeMonitorEntrada($i): void { $this->unsetIndex($this->form->monitor_entradas_rows, $i); }

    public function updatedFormEthernetTiene($value): void
    {
        if (!(bool)$value) $this->form->ethernet_es_gigabit = false;
    }

    public function updatedFormMonitorIncluido($value): void
    {
        if ($value !== 'SI') {
            $this->form->monitor_pulgadas = null;
            $this->form->monitor_resolucion = null;
            $this->form->monitor_entradas_rows = [];
        }
    }

    public function updatedFormTipoEquipo($value): void
    {
        $tipo = strtoupper(trim((string) $value));
        if (in_array($tipo, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true)) {
            $this->form->monitor_incluido = null;
            $this->form->monitor_pulgadas = null;
            $this->form->monitor_resolucion = null;
            $this->form->monitor_entradas_rows = [];
        }
        if (in_array($tipo, ['ESCRITORIO','MICRO PC'], true)) {
            $this->form->pantalla_pulgadas = null;
            $this->form->pantalla_resolucion = null;
            $this->form->pantalla_es_touch = false;
        }
    }

    public function updatedFormGpuIntegradaTiene(): void
    {
        if ($this->isLaptopLikeTipo($this->form->tipo_equipo)) { $this->form->gpu_integrada_tiene = true; return; }
        if (!$this->form->gpu_integrada_tiene) {
            $this->form->gpu_integrada_marca = null;
            $this->form->gpu_integrada_modelo = null;
            $this->form->gpu_integrada_vram = null;
        }
    }

    public function updatedFormGpuDedicadaTiene(): void
    {
        if (!$this->form->gpu_dedicada_tiene) {
            $this->form->gpu_dedicada_marca = null;
            $this->form->gpu_dedicada_modelo = null;
            $this->form->gpu_dedicada_vram = null;
            $this->form->gpu_dedicada_vram_unidad = 'GB';
        }
    }

    public function updatedFormGpuIntegradaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_integrada_marca, ['INTEL','AMD','NVIDIA'], true))
            $this->form->gpu_integrada_marca = '';
    }

    public function updatedFormGpuDedicadaMarcaMode($value): void
    {
        if ($value === 'MANUAL' && in_array($this->form->gpu_dedicada_marca, ['INTEL','AMD','NVIDIA'], true))
            $this->form->gpu_dedicada_marca = '';
    }

    public function updatedFormRamEsSoldada($value): void
    {
        if (!$value) {
            $this->form->ram_cantidad_soldada = '';
            $this->form->ram_sin_slots = false;
            if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) $this->form->ram_slots_totales = '';
            if ($this->form->ram_expansion_max === '0 GB') $this->form->ram_expansion_max = '';
        }
    }

    public function updatedFormRamSinSlots($value): void
    {
        if ($value && !$this->form->ram_es_soldada) { $this->form->ram_sin_slots = false; return; }
        if ($value) { $this->form->ram_slots_totales = '0'; $this->form->ram_expansion_max = '0 GB'; return; }
        if ($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) $this->form->ram_slots_totales = '';
        if ($this->form->ram_expansion_max === '0 GB') $this->form->ram_expansion_max = '';
    }

    public function updatedFormRamSlotsTotales($value): void
    {
        $isZero = ($value === '0' || $value === 0);
        if ($isZero && !$this->form->ram_es_soldada) { $this->form->ram_slots_totales = ''; $this->form->ram_sin_slots = false; $this->form->ram_expansion_max = ''; return; }
        $this->form->ram_sin_slots = $isZero;
        if ($isZero) $this->form->ram_expansion_max = '0 GB';
        elseif ($this->form->ram_expansion_max === '0 GB') $this->form->ram_expansion_max = '';
    }

    public function updatedFormRamExpansionMax($value): void
    {
        if ($value === '0 GB' && !$this->form->ram_es_soldada) { $this->form->ram_expansion_max = ''; return; }
        if ($value === '0 GB') { $this->form->ram_slots_totales = '0'; $this->form->ram_sin_slots = true; return; }
        if (($this->form->ram_slots_totales === '0' || $this->form->ram_slots_totales === 0) && !$this->form->ram_sin_slots)
            $this->form->ram_slots_totales = '';
    }

    public function updatedFormBateriaTiene($value): void
    {
        if (!$value) { $this->form->bateria1_tipo = null; $this->form->bateria1_salud = null; }
    }

    public function updatedFormBateria2Tiene($value): void
    {
        if (!$value) { $this->form->bateria2_tipo = null; $this->form->bateria2_salud = null; }
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS (copiados de RegistrarEquipo)
    // ══════════════════════════════════════════════════════════════════════

    private function unsetIndex(array &$arr, $i): void
    {
        if (!isset($arr[$i])) return;
        unset($arr[$i]);
        $arr = array_values($arr);
    }

    private function tipoKey(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        return preg_replace('/\s+/', ' ', $v);
    }

    private function isLaptopLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['laptop','2 en 1','all in one','tablet','gamer'], true)
            || in_array(trim((string)$tipo), ['LAPTOP','2 EN 1','ALL IN ONE','TABLET','GAMER'], true);
    }

    private function isPcLikeTipo(?string $tipo): bool
    {
        return in_array($this->tipoKey($tipo), ['escritorio','micro pc'], true)
            || in_array(trim((string)$tipo), ['ESCRITORIO','MICRO PC'], true);
    }

    private function guardarBaterias(int $equipoId): void
    {
        $f = $this->form;
        EquipoBateria::where('equipo_id', $equipoId)->delete();
        if (!$f->bateria_tiene) return;
        if ($f->bateria1_tipo) EquipoBateria::create(['equipo_id' => $equipoId, 'tipo' => $f->bateria1_tipo, 'salud_percent' => $f->bateria1_salud ?: null, 'notas' => null]);
        if ($f->bateria2_tiene && $f->bateria2_tipo) EquipoBateria::create(['equipo_id' => $equipoId, 'tipo' => $f->bateria2_tipo, 'salud_percent' => $f->bateria2_salud ?: null, 'notas' => null]);
    }

    private function guardarMonitor(int $equipoId): void
    {
        $f = $this->form;
        if ($this->pantallaIntegrada) {
            EquipoMonitor::updateOrCreate(['equipo_id' => $equipoId], [
                'origen_pantalla' => 'INTEGRADA', 'incluido' => 1,
                'pulgadas' => $f->pantalla_pulgadas ?: null,
                'resolucion' => $f->pantalla_resolucion ?: null,
                'tipo_panel' => $f->pantalla_tipo ?: null,
                'es_touch' => (int)((bool)$f->pantalla_es_touch),
            ] + $this->monitorInputsPayload([]));
            return;
        }
        if (!$this->pantallaExterna) return;
        if ($f->monitor_incluido !== 'SI') {
            EquipoMonitor::updateOrCreate(['equipo_id' => $equipoId], ['origen_pantalla' => 'EXTERNA', 'incluido' => 0, 'pulgadas' => null, 'resolucion' => null, 'tipo_panel' => null, 'es_touch' => 0] + $this->monitorInputsPayload([]));
            return;
        }
        $counts = $this->aggregateCounters($f->monitor_entradas_rows ?? [], 'tipo', 'cantidad');
        EquipoMonitor::updateOrCreate(['equipo_id' => $equipoId], [
            'origen_pantalla' => 'EXTERNA', 'incluido' => 1,
            'pulgadas' => $f->monitor_pulgadas ?: null,
            'resolucion' => $f->monitor_resolucion ?: null,
            'tipo_panel' => $f->monitor_tipo_panel ?: null,
            'es_touch' => (int)((bool)$f->monitor_es_touch),
            'detalles_esteticos_checks' => $this->truncate($f->monitor_detalles_esteticos_checks ?: null, 255),
            'detalles_esteticos_otro' => $this->truncate($f->monitor_detalles_esteticos_otro ?: null, 255),
            'detalles_funcionamiento_checks' => $this->truncate($f->monitor_detalles_funcionamiento_checks ?: null, 255),
            'detalles_funcionamiento_otro' => $this->truncate($f->monitor_detalles_funcionamiento_otro ?: null, 255),
        ] + $this->monitorInputsPayload($counts));
    }

    private function guardarGpus(int $equipoId): void
    {
        $f = $this->form;
        EquipoGpu::where('equipo_id', $equipoId)->delete();
        $esLaptopLike = $this->isLaptopLikeTipo($f->tipo_equipo);
        if ($f->gpu_integrada_tiene || $esLaptopLike) {
            EquipoGpu::create(['equipo_id' => $equipoId, 'tipo' => 'INTEGRADA', 'activo' => 1, 'marca' => $f->gpu_integrada_marca ?: null, 'modelo' => $f->gpu_integrada_modelo ?: null, 'vram' => filled($f->gpu_integrada_vram) ? (int)$f->gpu_integrada_vram : null, 'vram_unidad' => filled($f->gpu_integrada_vram) ? ($f->gpu_integrada_vram_unidad ?: 'GB') : null, 'notas' => null]);
        }
        if ($f->gpu_dedicada_tiene) {
            EquipoGpu::create(['equipo_id' => $equipoId, 'tipo' => 'DEDICADA', 'activo' => 1, 'marca' => $f->gpu_dedicada_marca, 'modelo' => $f->gpu_dedicada_modelo, 'vram' => filled($f->gpu_dedicada_vram) ? (int)$f->gpu_dedicada_vram : null, 'vram_unidad' => filled($f->gpu_dedicada_vram) ? ($f->gpu_dedicada_vram_unidad ?: 'GB') : null, 'notas' => null]);
        }
    }

    private function monitorInputsPayload(array $countsByLabel): array
    {
        $payload = [];
        foreach (self::MAP_MONITOR_IN as $label => $col) {
            $qty = (int) ($countsByLabel[$label] ?? 0);
            $payload[$col] = $qty > 0 ? $qty : null;
        }
        return $payload;
    }

    private function applyAggregatesToEquipoColumns(): void
    {
        $f = $this->form;
        $usbCounts   = $this->aggregateCounters($f->puertos_usb ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($usbCounts, self::MAP_USB);
        $videoCounts = $this->aggregateCounters($f->puertos_video ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($videoCounts, self::MAP_VIDEO);
        $lectorCounts = $this->aggregateCounters($f->lectores ?? [], 'tipo', 'cantidad');
        $this->applyMapCountsToEquipo($lectorCounts, self::MAP_LECTORES);
    }

    private function aggregateCounters(array $rows, string $keyField, ?string $qtyField): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $k = trim((string) ($row[$keyField] ?? ''));
            if ($k === '') continue;
            $qty = $qtyField ? max((int) ($row[$qtyField] ?? 1), 1) : 1;
            $counts[$k] = ($counts[$k] ?? 0) + $qty;
        }
        return $counts;
    }

    private function applyMapCountsToEquipo(array $countsByLabel, array $map): void
    {
        $f = $this->form;
        foreach ($countsByLabel as $label => $count) {
            if (!isset($map[$label])) continue;
            $col = $map[$label];
            if ($f->{$col} === null) $f->{$col} = (string) $count;
        }
    }

    private function mapSlotsToDbColumns(): void
    {
        $f = $this->form;
        $f->slots_alm_ssd = $f->slots_alm_m2 = $f->slots_alm_m2_micro = $f->slots_alm_hdd = $f->slots_alm_msata = null;
        foreach (($f->slots_almacenamiento ?? []) as $row) {
            $tipo = trim((string) ($row['tipo'] ?? ''));
            if ($tipo === '' || !isset(self::MAP_SLOTS[$tipo])) continue;
            $cant = $row['cantidad'] ?? null;
            $valor = (is_numeric($cant) && (int)$cant > 0) ? (string)((int)$cant) : null;
            $f->{self::MAP_SLOTS[$tipo]} = $valor;
        }
    }

    private function buildChecksText(array $checks, ?string $otro): string
    {
        $checks = $checks ?? [];
        if (in_array('N/A', $checks, true)) { $checks = ['N/A']; $otro = null; }
        $txt = implode(', ', $checks);
        if (!empty($otro)) $txt .= ($txt ? ' | ' : '') . 'Otro: ' . $otro;
        return $txt;
    }

    private function truncate($value, int $max)
    {
        if ($value === null) return null;
        $value = (string) $value;
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }

    // ══════════════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════════════

    public function render()
    {
        return view('livewire.preparacion.equipos.mi-trabajo', [
            'tipo_equipo'          => $this->form->tipo_equipo ?? null,
            'catalogoPiezas'       => $this->catalogoPiezas,
            'categoriasDisponibles'=> $this->categoriasDisponibles,
            'detallesFuncionamientoCatalogo' => [
                'CAMARA NO FUNCIONA','NO FUNCIONA ENTRADA DE ETHERNET',
                'NO FUNCIONA 1 PUERTO USB','NO FUNCIONAN 2 PUERTOS USB',
                'NO FUNCIONA HDMI','NO FUNCIONA DISPLAY PORT','NO FUNCIONA MINI DISPLAY PORT',
                'NO FUNCIONA MINI HDMI','NO FUNCIONA VGA','NO FUNCIONA DVI',
                'FALLA ENTRADA TIPO-C','FALLA PLUG DE AUDIO',
                'RETROILUMINADO DE TECLADO NO FUNCIONA','TRACKPOINT NO FUNCIONA',
                'NO FUNCIONA BARRA DE DESPLAZAMIENTO','NO FUNCIONA LECTOR DE DISCO',
                'NO FUNCIONA SD','LIGERA DISTORCION BOCINA DERECHA',
                'LIGERA DISTORCION BOCINA IZQUIERDA','NO FUNCIONA MICROFONO',
                'NO FUNCIONA CLICS SUPERIORES','NO CUENTA CON PLUMA TOUCH ORIGINAL',
                'CONTRASEÑA EN BIOS',
            ],
        ]);
    }
}
