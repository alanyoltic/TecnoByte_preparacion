<?php

namespace App\Livewire\Preparacion\Garantias;

use App\Models\GarantiaProveedor;
use App\Models\LoteModeloRecibido;
use App\Models\Proveedor;
use App\Models\ClasificacionPuntos;
use App\Models\User;
use App\Services\GarantiaExternaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Garantías Externas'])]
class GestionGarantias extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────
    public string $busqueda        = '';
    public string $tabActivo       = 'por_enviar'; // por_enviar, en_tramite, resueltas
    public ?int   $filtroProveedor = null;

    // ── Modal de resolución ───────────────────────────────────────────────
    public bool  $modalResolucion   = false;
    public ?int  $garantiaResolverId = null;

    // Tipo de resolución elegido en el modal
    public string $tipoResolucion  = '';   // REPARADO | REEMPLAZADO | RECHAZADO_PROVEEDOR

    // Campos comunes
    public string $fechaResolucion  = '';
    public string $observaciones    = '';

    // Campos solo para REEMPLAZADO
    public string $numeroSerieNuevo      = '';
    public bool   $mismoModelo           = true;
    public ?int   $loteModeloNuevoId     = null;   // si mismoModelo = false y ya existe
    public bool   $crearNuevoModelo      = false;  // si mismoModelo = false y no existe en el lote
    public string $nuevoModeloMarca      = '';
    public string $nuevoModeloModelo     = '';
    public ?int   $nuevoModeloClasifId   = null;
    public int    $tecnicoReingresoId    = 0;

    // Error local del modal
    public string $errorModal = '';

    // ── Lifecycle ─────────────────────────────────────────────────────────

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->tienePermiso('prep.garantias.ver'),
            403
        );
        $this->fechaResolucion = now()->toDateString();
    }

    public function setTab(string $tab): void
    {
        $this->tabActivo = $tab;
        $this->resetPage();
    }

    // ── Datos computados ─────────────────────────────────────────────────

    #[Computed]
    public function garantias()
    {
        return GarantiaProveedor::with([
                'equipo.loteModelo.lote',
                'proveedor',
                'reportadoPor',
                'resueltoPor',
                'equipoNuevo',
                'tecnicoReingreso',
            ])
            ->when($this->tabActivo === 'por_enviar', fn ($q) => $q->where('estatus', GarantiaProveedor::PENDIENTE)->whereNull('fecha_envio'))
            ->when($this->tabActivo === 'en_tramite', fn ($q) => $q->where('estatus', GarantiaProveedor::PENDIENTE)->whereNotNull('fecha_envio'))
            ->when($this->tabActivo === 'resueltas', fn ($q) => $q->whereIn('estatus', [GarantiaProveedor::RESUELTA, GarantiaProveedor::CANCELADA]))
            ->when($this->filtroProveedor, fn ($q) => $q->where('proveedor_id', $this->filtroProveedor))
            ->when($this->busqueda, function ($q) {
                $q->whereHas('equipo', fn ($eq) =>
                    $eq->where('numero_serie', 'like', '%'.$this->busqueda.'%')
                       ->orWhere('marca', 'like', '%'.$this->busqueda.'%')
                       ->orWhere('modelo', 'like', '%'.$this->busqueda.'%')
                );
            })
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa', 'abreviacion']);
    }

    #[Computed]
    public function tecnicosDisponibles()
    {
        return User::whereHas('role', fn ($q) => $q->whereIn('slug', ['tecnico', 'lider', 'gerente']))
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno']);
    }

    #[Computed]
    public function clasificaciones()
    {
        return ClasificacionPuntos::orderBy('nombre')->get(['id', 'nombre']);
    }

    /** Garantía que se está resolviendo actualmente */
    #[Computed]
    public function garantiaActual(): ?GarantiaProveedor
    {
        if (! $this->garantiaResolverId) return null;
        return GarantiaProveedor::with(['equipo.loteModelo', 'proveedor', 'reportadoPor'])->find($this->garantiaResolverId);
    }

    /** Modelos disponibles en el mismo lote del equipo actual (para cambio de modelo) */
    #[Computed]
    public function modelosEnLote(): array
    {
        $garantia = $this->garantiaActual;
        if (! $garantia) return [];

        $loteId = $garantia->equipo?->loteModelo?->lote_id;
        if (! $loteId) return [];

        return LoteModeloRecibido::where('lote_id', $loteId)
            ->get(['id', 'marca', 'modelo'])
            ->map(fn ($lm) => ['id' => $lm->id, 'label' => "{$lm->marca} {$lm->modelo}"])
            ->toArray();
    }

    /** Contadores para el encabezado */
    #[Computed]
    public function contadores(): array
    {
        return [
            'por_enviar' => GarantiaProveedor::where('estatus', GarantiaProveedor::PENDIENTE)->whereNull('fecha_envio')->count(),
            'en_tramite' => GarantiaProveedor::where('estatus', GarantiaProveedor::PENDIENTE)->whereNotNull('fecha_envio')->count(),
            'resueltas'  => GarantiaProveedor::whereIn('estatus', [GarantiaProveedor::RESUELTA, GarantiaProveedor::CANCELADA])->count(),
        ];
    }

    // ── Acciones de filtrado ──────────────────────────────────────────────

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroProveedor(): void
    {
        $this->resetPage();
    }

    // ── Acciones principales ──────────────────────────────────────────────

    public function marcarComoEnviado(int $garantiaId): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.garantias.gestionar'), 403);

        $garantia = GarantiaProveedor::find($garantiaId);
        if ($garantia && $garantia->esPendiente() && is_null($garantia->fecha_envio)) {
            $garantia->update(['fecha_envio' => now()->toDateString()]);
            $this->dispatch('toast', type: 'success', title: 'Garantía enviada', message: 'El equipo ha sido marcado como enviado al proveedor.');
        }
    }

    // ── Modal de resolución ───────────────────────────────────────────────

    public function abrirModal(int $garantiaId): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.garantias.gestionar'), 403);

        $this->garantiaResolverId  = $garantiaId;
        $this->tipoResolucion      = '';
        $this->fechaResolucion     = now()->toDateString();
        $this->observaciones       = '';
        $this->numeroSerieNuevo    = '';
        $this->mismoModelo         = true;
        $this->loteModeloNuevoId   = null;
        $this->crearNuevoModelo    = false;
        $this->nuevoModeloMarca    = '';
        $this->nuevoModeloModelo   = '';
        $this->nuevoModeloClasifId = null;
        $this->errorModal          = '';

        // Pre-asignar al técnico que reportó
        $garantia = $this->garantiaActual;
        $this->tecnicoReingresoId = $garantia?->reportado_por_id ?? 0;

        $this->modalResolucion = true;

        unset($this->garantiaActual, $this->modelosEnLote);
    }

    public function cerrarModal(): void
    {
        $this->modalResolucion    = false;
        $this->garantiaResolverId = null;
    }

    public function guardarResolucion(GarantiaExternaService $service): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.garantias.gestionar'), 403);

        $this->errorModal = '';

        $garantia = GarantiaProveedor::find($this->garantiaResolverId);
        if (! $garantia || ! $garantia->esPendiente()) {
            $this->errorModal = 'Esta garantía ya fue resuelta o no existe.';
            return;
        }

        // Validación según tipo de resolución
        if (! $this->tipoResolucion) {
            $this->errorModal = 'Selecciona el tipo de resolución.';
            return;
        }

        if (! $this->fechaResolucion) {
            $this->errorModal = 'Indica la fecha de resolución.';
            return;
        }

        try {
            $datos = [
                'fecha_resolucion' => $this->fechaResolucion,
                'observaciones'    => trim($this->observaciones) ?: null,
            ];

            match ($this->tipoResolucion) {

                GarantiaProveedor::REPARADO => $service->resolverReparado($garantia, $datos),

                GarantiaProveedor::REEMPLAZADO => $this->resolverReemplazadoConValidacion($service, $garantia, $datos),

                GarantiaProveedor::RECHAZADO_PROVEEDOR => $service->resolverRechazado($garantia, $datos),
            };

            $this->cerrarModal();
            $this->dispatch('toast', type: 'success', title: 'Garantía resuelta', message: 'El equipo volvió al flujo de preparación.');

            // Refrescar datos computados
            unset($this->garantias, $this->contadores);

        } catch (\Throwable $e) {
            $this->errorModal = $e->getMessage();
            \Log::error('GestionGarantias::guardarResolucion', [
                'garantia_id' => $this->garantiaResolverId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
        }
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    private function resolverReemplazadoConValidacion(
        GarantiaExternaService $service,
        GarantiaProveedor      $garantia,
        array                  $datos
    ): void {
        if (! trim($this->numeroSerieNuevo)) {
            throw new \RuntimeException('Ingresa el número de serie del equipo nuevo.');
        }

        if (! $this->tecnicoReingresoId) {
            throw new \RuntimeException('Selecciona el técnico al que se asignará el equipo nuevo.');
        }

        if (! $this->mismoModelo) {
            if (! $this->loteModeloNuevoId && ! $this->crearNuevoModelo) {
                throw new \RuntimeException('Selecciona el modelo del equipo nuevo o indica que se debe crear uno nuevo.');
            }

            if ($this->crearNuevoModelo) {
                if (! trim($this->nuevoModeloMarca) || ! trim($this->nuevoModeloModelo)) {
                    throw new \RuntimeException('Ingresa la marca y modelo del nuevo equipo.');
                }
                if (! $this->nuevoModeloClasifId) {
                    throw new \RuntimeException('Selecciona la clasificación de puntos para el nuevo modelo.');
                }
            }
        }

        $datos = array_merge($datos, [
            'numero_serie_nuevo'           => trim($this->numeroSerieNuevo),
            'mismo_modelo'                 => $this->mismoModelo,
            'lote_modelo_nuevo_id'         => $this->mismoModelo ? null : ($this->crearNuevoModelo ? null : $this->loteModeloNuevoId),
            'nuevo_modelo_marca'           => $this->crearNuevoModelo ? trim($this->nuevoModeloMarca) : null,
            'nuevo_modelo_modelo'          => $this->crearNuevoModelo ? trim($this->nuevoModeloModelo) : null,
            'nuevo_modelo_clasificacion_id'=> $this->crearNuevoModelo ? $this->nuevoModeloClasifId : null,
            'tecnico_reingreso_id'         => $this->tecnicoReingresoId,
        ]);

        $service->resolverReemplazado($garantia, $datos);
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.preparacion.garantias.gestion-garantias');
    }
}
