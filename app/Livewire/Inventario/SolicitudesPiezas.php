<?php

namespace App\Livewire\Inventario;

use App\Models\SolicitudPieza;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Mis Solicitudes de Piezas'])]
class SolicitudesPiezas extends Component
{
    use WithPagination;

    public string $filtroEstatus = 'PENDIENTE';
    public string $busqueda = '';
    public bool $esTecnico = false;

    public bool $modalConfirmar = false;
    public ?SolicitudPieza $solicitudSeleccionada = null;
    public bool $funciono = true;
    public string $notasConfirmacion = '';

    public bool $modalEliminar  = false;
    public ?int $eliminarSolicitudId = null;

    protected $queryString = ['filtroEstatus', 'busqueda'];

    public function mount(): void
    {
        $this->autorizarSolicitudes();

        $roleSlug = strtolower((string) (auth()->user()?->role?->slug ?? ''));
        $this->esTecnico = ($roleSlug === 'tecnico');
    }

    public function render()
    {
        $this->autorizarSolicitudes();

        $authId = auth()->id();

        $solicitudes = SolicitudPieza::query()
            ->with([
                'catalogoPieza',
                'inventarioPieza.almacen',
                'respondidaPor',
                'reasignadoA',
                'solicitadoPor',
                'asignacionEquipo.equipo',
                'equipo',
                'intentos.asignadoA',
                'intentos.inventarioPieza.almacen',
                'intentos.asignadoPor',
            ])
            ->where(function ($q) use ($authId) {
                $q->where('solicitado_por_id', $authId)
                  ->orWhere('reasignado_a_id', $authId);
            })
            ->when($this->filtroEstatus !== 'TODAS', function ($q) use ($authId) {
                match ($this->filtroEstatus) {
                    // Tecnico tabs
                    'POR_RESTOCK'     => $q->whereIn('estatus', [SolicitudPieza::PENDIENTE_COMPRA, SolicitudPieza::COMPRADA])
                                           ->where(fn ($q2) => $q2->whereNull('reasignado_a_id')->orWhereColumn('reasignado_a_id', 'solicitado_por_id')),
                    'EN_CALIDAD'      => $q->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true),
                    'FINALIZADO_TEC'  => $q->where(fn ($q2) =>
                                            $q2->where('estatus', SolicitudPieza::CANCELADA)
                                               ->orWhere(fn ($q3) => $q3->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', false))
                                               ->orWhere(fn ($q4) => $q4->whereNotNull('reasignado_a_id')
                                                                        ->whereColumn('reasignado_a_id', '!=', 'solicitado_por_id')
                                                                        ->where('solicitado_por_id', $authId))
                                        ),
                    'REQUIERE_REASIGNACION' => $q->where('estatus', SolicitudPieza::REQUIERE_REASIGNACION),
                    // Standard status filter (for lider tabs and tecnico PENDIENTE/SURTIDA_INVENTARIO)
                    default           => $q->where('estatus', $this->filtroEstatus)
                                           ->when(
                                               $this->esTecnico && !in_array($this->filtroEstatus, [SolicitudPieza::CONFIRMADA, SolicitudPieza::CANCELADA]),
                                               fn ($q2) => $q2->where(fn ($q3) => $q3->whereNull('reasignado_a_id')->orWhereColumn('reasignado_a_id', 'solicitado_por_id'))
                                           ),
                };
            })
            ->when($this->busqueda, function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('catalogoPieza', fn ($c) =>
                        $c->where('nombre', 'like', '%' . $this->busqueda . '%')
                    )
                    ->orWhere('descripcion_libre', 'like', '%' . $this->busqueda . '%')
                    ->orWhereHas('equipo', function ($e) {
                        $bId = is_numeric(trim($this->busqueda)) ? (int) trim($this->busqueda) : -1;
                        $e->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                          ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                          ->orWhere('id', $bId);
                    })
                    ->orWhereHas('asignacionEquipo.equipo', function ($e) {
                        $bId = is_numeric(trim($this->busqueda)) ? (int) trim($this->busqueda) : -1;
                        $e->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                          ->orWhere('modelo', 'like', '%' . $this->busqueda . '%')
                          ->orWhere('id', $bId);
                    });
                });
            })
            ->orderByRaw("FIELD(estatus, 'SURTIDA_INVENTARIO', 'COMPRADA', 'PENDIENTE', 'REQUIERE_REASIGNACION', 'PENDIENTE_COMPRA', 'CONFIRMADA', 'CANCELADA')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // ── Contadores ────────────────────────────────────────────────────────
        $base = SolicitudPieza::where(function ($q) use ($authId) {
            $q->where('solicitado_por_id', $authId)
              ->orWhere('reasignado_a_id', $authId);
        });

        // Delegadas: creadas por mí pero asignadas a otro
        $delegadasCount = (clone $base)
            ->where('solicitado_por_id', $authId)
            ->whereNotNull('reasignado_a_id')
            ->whereColumn('reasignado_a_id', '!=', 'solicitado_por_id')
            ->count();

        // Base sin delegadas (para tabs que no las incluyen)
        $baseSinDel = (clone $base)->where(fn ($q) =>
            $q->whereNull('reasignado_a_id')
              ->orWhereColumn('reasignado_a_id', 'solicitado_por_id')
        );

        $contadores = [
            // Tecnico
            'por_confirmar'      => (clone $baseSinDel)->where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'restock'            => (clone $baseSinDel)->whereIn('estatus', [SolicitudPieza::PENDIENTE_COMPRA, SolicitudPieza::COMPRADA])->count(),
            'lista'              => (clone $baseSinDel)->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'en_calidad'         => (clone $base)->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', true)->count(),
            'reasignacion'       => (clone $base)->where('estatus', SolicitudPieza::REQUIERE_REASIGNACION)->count(),
            'finalizado_tec'     => (clone $base)->where(fn ($q) =>
                                        $q->where('estatus', SolicitudPieza::CANCELADA)
                                          ->orWhere(fn ($q2) => $q2->where('estatus', SolicitudPieza::CONFIRMADA)->where('funciono', false))
                                    )->count() + $delegadasCount,
            // Lider (old tabs)
            'pendientes'        => (clone $base)->where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas'          => (clone $base)->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra' => (clone $base)->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas'         => (clone $base)->where('estatus', SolicitudPieza::COMPRADA)->count(),
            'confirmadas'       => (clone $base)->where('estatus', SolicitudPieza::CONFIRMADA)->count(),
            'canceladas'        => (clone $base)->where('estatus', SolicitudPieza::CANCELADA)->count(),
            // Todos
            'todas'             => (clone $base)->count(),
        ];

        return view('livewire.inventario.solicitudes-piezas', compact('solicitudes', 'contadores'));
    }

    public function cambiarFiltro(string $estatus): void
    {
        $this->filtroEstatus = $estatus;
        $this->resetPage();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirModalEliminar(int $id): void
    {
        $solicitud = SolicitudPieza::findOrFail($id);

        if ((int) $solicitud->solicitado_por_id !== (int) auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'Solo quien creo la solicitud puede eliminarla.');
            return;
        }

        if ($solicitud->estatus !== SolicitudPieza::PENDIENTE) {
            $this->dispatch('toast', type: 'error', message: 'Solo se pueden eliminar solicitudes que aun esten pendientes.');
            return;
        }

        $this->eliminarSolicitudId = $id;
        $this->modalEliminar = true;
    }

    public function confirmarEliminar(): void
    {
        if (!$this->eliminarSolicitudId) return;

        $solicitud = SolicitudPieza::findOrFail($this->eliminarSolicitudId);

        if ((int) $solicitud->solicitado_por_id !== (int) auth()->id()
            || $solicitud->estatus !== SolicitudPieza::PENDIENTE) {
            $this->dispatch('toast', type: 'error', message: 'No se puede eliminar esta solicitud.');
            $this->cerrarModalEliminar();
            return;
        }

        $solicitud->delete();

        $this->dispatch('toast', type: 'success', message: 'Solicitud eliminada. El equipo sigue marcado como pieza pendiente en tus asignaciones.');
        $this->cerrarModalEliminar();
    }

    public function cerrarModalEliminar(): void
    {
        $this->modalEliminar = false;
        $this->eliminarSolicitudId = null;
    }

    public function abrirConfirmar(int $id): void
    {
        $solicitud = SolicitudPieza::with(['inventarioPieza.almacen', 'equipo', 'asignacionEquipo.equipo'])
            ->findOrFail($id);

        if (!$this->esResponsableDeSolicitud($solicitud)) {
            $this->dispatch('toast', type: 'error', message: 'Solo el tecnico responsable puede gestionar esta instalacion.');
            return;
        }

        if ($solicitud->estatus !== SolicitudPieza::SURTIDA_INVENTARIO) {
            $this->dispatch('toast', type: 'error', message: 'La pieza aun no esta lista para instalar.');
            return;
        }

        $this->solicitudSeleccionada = $solicitud;
        $this->funciono = true;
        $this->notasConfirmacion = '';
        $this->modalConfirmar = true;
    }

    public function iniciarDesdeModal(): void
    {
        if (!$this->solicitudSeleccionada) return;

        if (!$this->esResponsableDeSolicitud($this->solicitudSeleccionada)) {
            $this->dispatch('toast', type: 'error', message: 'No tienes permiso.');
            return;
        }

        $this->solicitudSeleccionada->update(['iniciada_instalacion_en' => now()]);
        $this->solicitudSeleccionada = $this->solicitudSeleccionada->fresh(['inventarioPieza.almacen', 'equipo', 'asignacionEquipo.equipo']);
    }

    public function confirmarConResultado(bool $funciono): void
    {
        if (!$this->solicitudSeleccionada || !$this->esResponsableDeSolicitud($this->solicitudSeleccionada)) {
            $this->dispatch('toast', type: 'error', message: 'No tienes permiso para cerrar esta solicitud.');
            $this->cerrarModal();
            return;
        }

        try {
            $this->solicitudSeleccionada->finalizarInstalacionPorTecnico(
                auth()->id(),
                $funciono,
                null
            );

            $this->dispatch('toast', type: 'success', message: $funciono
                ? 'Instalacion confirmada. El equipo volvio al flujo de calidad.'
                : 'Se registro la falla. El gerente asignara una nueva pieza para reintentar.'
            );

            $this->cerrarModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error al confirmar: ' . $e->getMessage());
        }
    }

    public function confirmarInstalacion(): void
    {
        $this->confirmarConResultado($this->funciono);
    }

    public function cerrarModal(): void
    {
        $this->modalConfirmar = false;
        $this->solicitudSeleccionada = null;
        $this->funciono = true;
        $this->notasConfirmacion = '';
    }

    private function autorizarSolicitudes(): void
    {
        $user = auth()->user();
        $roleSlug = strtolower((string) ($user?->role?->slug ?? ''));

        abort_unless(
            $user?->tienePermiso('prep.equipos.ver') && in_array($roleSlug, ['lider', 'tecnico'], true),
            403
        );
    }

    private function esResponsableDeSolicitud(SolicitudPieza $solicitud): bool
    {
        $responsableId = $solicitud->reasignado_a_id ?: $solicitud->solicitado_por_id;

        return (int) $responsableId === (int) auth()->id();
    }
}
