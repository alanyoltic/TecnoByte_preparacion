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

    public bool $modalRetrabajo = false;

    public ?int $equipoSeleccionadoId = null;

    // Formulario retrabajo
    public ?int $retrabajoTecnicoId = null;
    public string $retrabajoNotas = 'RETRABAJO';

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
    // RETRABAJO / MEJORA
    // ──────────────────────────────────────────────────────────────────────

    public function abrirRetrabajo(int $equipoId): void
    {
        $equipo = Equipo::findOrFail($equipoId);

        // Validar estatus permitidos para retrabajo/reasignación
        $estatusPermitidos = [Equipo::AREA_SIN_ASIGNAR, Equipo::AREA_EN_CALIDAD, Equipo::AREA_FINALIZADO];
        
        if (!in_array($equipo->estatus_area, $estatusPermitidos)) {
            $this->dispatch('toast', type: 'error', message: 'El equipo no está en una etapa válida para reasignarse.');
            return;
        }

        // Validar que no tenga otra asignación activa simultáneamente
        $activa = \App\Models\AsignacionEquipo::where('equipo_id', $equipo->id)
            ->whereIn('camino', [\App\Models\AsignacionEquipo::PENDIENTE, \App\Models\AsignacionEquipo::PRE_ASIGNADO, \App\Models\AsignacionEquipo::EN_PROCESO])
            ->exists();
            
        if ($activa) {
            $this->dispatch('toast', type: 'error', message: 'El equipo ya tiene una asignación en curso (Duplicidad bloqueada).');
            return;
        }

        $this->equipoSeleccionadoId = $equipoId;
        $this->retrabajoTecnicoId = null;
        $this->retrabajoNotas = 'RETRABAJO';
        $this->error = '';
        $this->modalRetrabajo = true;
    }

    public function confirmarRetrabajo(): void
    {
        $this->error = '';

        if (!$this->retrabajoTecnicoId) {
            $this->error = 'Debes seleccionar un técnico.';
            return;
        }

        try {
            $equipo = Equipo::findOrFail($this->equipoSeleccionadoId);
            
            // Re-validación de seguridad estricta antes de guardar
            $estatusPermitidos = [Equipo::AREA_SIN_ASIGNAR, Equipo::AREA_EN_CALIDAD, Equipo::AREA_FINALIZADO];
            if (!in_array($equipo->estatus_area, $estatusPermitidos)) {
                throw new \Exception('El equipo no está en una etapa válida para reasignarse.');
            }

            $activa = \App\Models\AsignacionEquipo::where('equipo_id', $equipo->id)
                ->whereIn('camino', [\App\Models\AsignacionEquipo::PENDIENTE, \App\Models\AsignacionEquipo::PRE_ASIGNADO, \App\Models\AsignacionEquipo::EN_PROCESO])
                ->exists();
                
            if ($activa) {
                throw new \Exception('El equipo ya tiene una asignación activa.');
            }
            
            \DB::transaction(function() use ($equipo) {
                $asignacion = \App\Models\Asignacion::create([
                    'tecnico_id' => $this->retrabajoTecnicoId,
                    'asignado_por_id' => auth()->id(),
                    'lote_modelo_id' => $equipo->lote_modelo_id,
                    'cantidad' => 1,
                    'fecha_asignacion' => \Carbon\Carbon::today(),
                    'estatus' => \App\Models\Asignacion::PENDIENTE,
                    'notas' => trim($this->retrabajoNotas) ?: 'RETRABAJO',
                ]);
                
                // Creamos una NUEVA AsignacionEquipo, conservando el historial de la anterior
                \App\Models\AsignacionEquipo::create([
                    'asignacion_id' => $asignacion->id,
                    'equipo_id' => $equipo->id,
                    'camino' => \App\Models\AsignacionEquipo::PRE_ASIGNADO,
                    'pre_asignado' => true,
                ]);
                
                $equipo->update([
                    'estatus_ciclo' => Equipo::CICLO_PREPARACION, 
                    'estatus_area' => Equipo::AREA_ASIGNADO
                ]);
            });

            $this->dispatch('toast',
                type: 'success',
                title: 'Reasignado',
                message: 'El equipo ha sido reasignado para retrabajo/mejora.'
            );

            $this->cerrarModales();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->error = "Error al reasignar: {$e->getMessage()}";
            \Log::error('confirmarRetrabajo error: '.$e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────

    public function cerrarModales(): void
    {
        $this->modalValidar = false;
        $this->modalRechazar = false;
        $this->modalRetrabajo = false;
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

    #[\Livewire\Attributes\Computed]
    public function tecnicos()
    {
        return \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['tecnico', 'lider']);
            })
            ->orderBy('nombre')
            ->get();
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
