<?php

namespace App\Livewire\Preparacion\Calidad;

use App\Models\Equipo;
use App\Models\ValidacionCalidad;
use App\Services\CalidadService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Gestión de Calidad'])]
class GestionCalidad extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ──────────────────────────────────────────────────────────────────────
    // STATE & FILTERS
    // ──────────────────────────────────────────────────────────────────────

    public string $search = '';

    public string $filtroEstado = 'en_calidad'; // 'en_calidad' | 'validados' | 'rechazados' | 'todos'

    public string $filtroValidador = '';

    public int $perPage = 25;

    // Modales
    public bool $modalValidar = false;

    public bool $modalRechazar = false;

    public ?int $equipoSeleccionadoId = null;

    // Formulario validación
    public ?int $calificacion = null;

    public array $qSalioBien = [];

    public string $notasValidacion = '';

    // Formulario rechazo
    public string $motivoRechazo = '';

    public array $qSalioMal = [];

    public array $qSalioBienRechazo = [];

    public ?int $calificacionRechazo = null;

    public string $notasRechazo = '';

    // Errores
    public string $error = '';

    public string $errorRechazo = '';

    // ──────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ──────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->autorizarAcceso();
    }

    private function autorizarAcceso(): void
    {
        abort_unless(
            auth()->user()?->tienePermiso('prep.calidad.validar'),
            403
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // ACCIONES PRINCIPALES
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Abre modal para validar (aprobar) un equipo
     */
    public function abrirValidar(int $equipoId): void
    {
        $equipo = Equipo::findOrFail($equipoId);

        if ($equipo->estatus_area !== Equipo::AREA_EN_CALIDAD) {
            $this->dispatch('toast', type: 'error', message: 'Equipo no está en calidad.');

            return;
        }

        $this->equipoSeleccionadoId = $equipoId;
        $this->resetFormValidacion();
        $this->modalValidar = true;
    }

    /**
     * Ejecuta validación (aprobación)
     */
    public function validarEquipo(): void
    {
        $this->error = '';

        try {
            $service = new CalidadService;

            $validacion = $service->validarEquipo(
                equipoId: $this->equipoSeleccionadoId,
                calificacion: $this->calificacion,
                qsalioBien: array_filter($this->qSalioBien),
                notas: trim($this->notasValidacion) ?: null
            );

            $this->dispatch('toast',
                type: 'success',
                title: 'Equipo validado',
                message: 'El equipo ha sido aprobado y está listo para ventas.'
            );

            $this->cerrarModales();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->error = "Error al validar: {$e->getMessage()}";
            \Log::error('validarEquipo error: '.$e->getMessage());
        }
    }

    /**
     * Abre modal para rechazar un equipo
     */
    public function abrirRechazar(int $equipoId): void
    {
        $equipo = Equipo::findOrFail($equipoId);

        if ($equipo->estatus_area !== Equipo::AREA_EN_CALIDAD) {
            $this->dispatch('toast', type: 'error', message: 'Equipo no está en calidad.');

            return;
        }

        $this->equipoSeleccionadoId = $equipoId;
        $this->resetFormRechazo();
        $this->modalRechazar = true;
    }

    /**
     * Ejecuta rechazo
     */
    public function rechazarEquipo(): void
    {
        $this->errorRechazo = '';

        // Validar motivo obligatorio
        if (empty(trim($this->motivoRechazo))) {
            $this->errorRechazo = 'El motivo del rechazo es obligatorio.';

            return;
        }

        try {
            $service = new CalidadService;

            $validacion = $service->rechazarEquipo(
                equipoId: $this->equipoSeleccionadoId,
                motivo: trim($this->motivoRechazo),
                qSalioMal: array_filter($this->qSalioMal),
                qSalioBien: array_filter($this->qSalioBienRechazo),
                calificacion: $this->calificacionRechazo,
                notas: trim($this->notasRechazo) ?: null
            );

            $this->dispatch('toast',
                type: 'warning',
                title: 'Equipo rechazado',
                message: 'El equipo ha vuelto a preparación. El técnico recibirá la notificación.'
            );

            $this->cerrarModales();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->errorRechazo = "Error al rechazar: {$e->getMessage()}";
            \Log::error('rechazarEquipo error: '.$e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────

    public function cerrarModales(): void
    {
        $this->modalValidar = false;
        $this->modalRechazar = false;
        $this->equipoSeleccionadoId = null;
    }

    private function resetFormValidacion(): void
    {
        $this->calificacion = null;
        $this->qSalioBien = [];
        $this->notasValidacion = '';
        $this->error = '';
    }

    private function resetFormRechazo(): void
    {
        $this->motivoRechazo = '';
        $this->qSalioMal = [];
        $this->qSalioBienRechazo = [];
        $this->calificacionRechazo = null;
        $this->notasRechazo = '';
        $this->errorRechazo = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    // ──────────────────────────────────────────────────────────────────────
    // QUERIES
    // ──────────────────────────────────────────────────────────────────────

    #[\Livewire\Attributes\Computed]
    public function equipos()
    {
        $query = Equipo::query()
            ->with([
                'loteModelo.lote.proveedor',
                'registradoPor' => fn ($q) => $q->withoutGlobalScopes(),
                'validacionesCalidad' => fn ($q) => $q->latest(),
                'asignacionEquipos' => fn ($q) => $q->latest('fin_en'),
            ])
            ->orderByDesc('created_at');

        // Filtro por estado
        if ($this->filtroEstado === 'en_calidad') {
            $query->where('estatus_area', Equipo::AREA_EN_CALIDAD);
        } elseif ($this->filtroEstado === 'validados') {
            $query->whereIn('estatus_area', [Equipo::AREA_FINALIZADO])
                ->whereHas('validacionesCalidad', function ($q) {
                    $q->where('estado', ValidacionCalidad::APROBADO);
                });
        } elseif ($this->filtroEstado === 'rechazados') {
            $query->where('estatus_area', Equipo::AREA_EN_PROCESO)
                ->whereHas('validacionesCalidad', function ($q) {
                    $q->where('estado', ValidacionCalidad::RECHAZADO);
                });
        }
        // 'todos' no agrega filtro

        // Búsqueda
        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';
            $rawId = trim($this->search);

            $query->where(function ($q) use ($search, $rawId) {
                $q->where('numero_serie', 'like', $search)
                    ->orWhere('marca', 'like', $search)
                    ->orWhere('modelo', 'like', $search)
                    ->orWhere('id', is_numeric($rawId) ? (int) $rawId : -1);
            });
        }

        // Filtro por validador
        if ($this->filtroValidador !== '') {
            $query->whereHas('validacionesCalidad', function ($q) {
                $q->where('validado_por', $this->filtroValidador);
            });
        }

        return $query->paginate($this->perPage);
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            'en_calidad' => Equipo::where('estatus_area', Equipo::AREA_EN_CALIDAD)->count(),
            'validados' => Equipo::where('estatus_area', Equipo::AREA_FINALIZADO)
                ->whereHas('validacionesCalidad', function ($q) {
                    $q->where('estado', ValidacionCalidad::APROBADO);
                })->count(),
            'rechazados' => ValidacionCalidad::where('estado', ValidacionCalidad::RECHAZADO)->count(),
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function validadores()
    {
        return \App\Models\User::whereHas('validacionesCalidad')
            ->pluck('nombre', 'id')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.preparacion.calidad.gestion-calidad', [
            'equipos' => $this->equipos,
            'stats' => $this->stats,
            'validadores' => $this->validadores,
        ]);
    }
}
