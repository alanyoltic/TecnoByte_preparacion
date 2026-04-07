<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Proveedor;
use App\Models\SolicitudPieza;
use Livewire\Component;
use Livewire\WithPagination;

class PendientesPiezas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';
    public string $filtroEstatus = 'todos';
    public string $filtroProveedor = 'todos';

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.inventario.ver'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstatus(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroProveedor(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();

        $query = SolicitudPieza::query()
            ->with([
                'equipo.loteModelo.lote.proveedor',
                'asignacionEquipo.equipo.loteModelo.lote.proveedor',
                'catalogoPieza',
                'solicitadoPor',
                'respondidaPor',
                'reasignadoA',
            ])
            ->whereNotIn('estatus', [SolicitudPieza::CONFIRMADA, SolicitudPieza::CANCELADA]);

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->whereHas('equipo', function ($qe) use ($search) {
                    $qe->where('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search)
                        ->orWhere('tipo_equipo', 'like', $search);
                })->orWhereHas('asignacionEquipo.equipo', function ($qe) use ($search) {
                    $qe->where('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search)
                        ->orWhere('tipo_equipo', 'like', $search);
                })->orWhereHas('catalogoPieza', function ($qp) use ($search) {
                    $qp->where('nombre', 'like', $search);
                })->orWhere('descripcion_libre', 'like', $search);
            });
        }

        if ($this->filtroEstatus !== 'todos') {
            $query->where('estatus', $this->filtroEstatus);
        }

        if ($this->filtroProveedor !== 'todos') {
            $proveedorId = $this->filtroProveedor;

            $query->where(function ($q) use ($proveedorId) {
                $q->whereHas('equipo.loteModelo.lote', function ($qe) use ($proveedorId) {
                    $qe->where('proveedor_id', $proveedorId);
                })->orWhereHas('asignacionEquipo.equipo.loteModelo.lote', function ($qe) use ($proveedorId) {
                    $qe->where('proveedor_id', $proveedorId);
                });
            });
        }

        $solicitudes = (clone $query)
            ->orderByRaw("FIELD(estatus, 'PENDIENTE', 'PENDIENTE_COMPRA', 'COMPRADA', 'SURTIDA_INVENTARIO')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $registrosActivos = (clone $query)->get();

        $stats = [
            'total_equipos' => $registrosActivos
                ->map(fn (SolicitudPieza $solicitud) => $solicitud->equipo_relacionado?->id)
                ->filter()
                ->unique()
                ->count(),
            'pendiente_compra' => $registrosActivos->where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas' => $registrosActivos->where('estatus', SolicitudPieza::COMPRADA)->count(),
            'surtidas' => $registrosActivos->where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
        ];

        return view('livewire.preparacion.inventario.pendientes-piezas', [
            'piezasPendientes' => $solicitudes,
            'stats' => $stats,
            'proveedores' => $proveedores,
        ]);
    }
}
