<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SolicitudPieza;
use App\Models\InventarioPieza;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GestionSolicitudesPiezas extends Component
{
    use WithPagination;

    public $filtroEstatus = 'PENDIENTE';
    public $busqueda = '';
    
    // Modal de surtir del inventario
    public $modalSurtir = false;
    public $solicitudSeleccionada = null;
    public $piezasDisponibles = [];
    public $piezaSeleccionada = null;
    public $notasRespuesta = '';
    public $tecnicoReasignadoId = null;
    public $tecnicos = [];
    
    // Modal de pendiente de compra
    public $modalCompra = false;
    
    // Modal de cancelar
    public $modalCancelar = false;
    public $motivoCancelacion = '';

    protected $queryString = ['filtroEstatus', 'busqueda'];

    public function render()
    {
        $solicitudes = SolicitudPieza::query()
            ->with([
                'asignacionEquipo.equipo',
                'equipo',
                'catalogoPieza',
                'solicitadoPor',
                'inventarioPieza.almacen',
                'respondidaPor'
            ])
            ->when($this->filtroEstatus !== 'TODAS', function ($query) {
                $query->where('estatus', $this->filtroEstatus);
            })
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('asignacionEquipo.equipo', function ($eq) {
                        $eq->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                           ->orWhere('modelo', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhereHas('equipo', function ($eq) {
                        $eq->where('numero_serie', 'like', '%' . $this->busqueda . '%')
                           ->orWhere('modelo', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhereHas('solicitadoPor', function ($tec) {
                        $tec->where('nombre', 'like', '%' . $this->busqueda . '%')
                            ->orWhere('apellido_paterno', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhereHas('catalogoPieza', function ($cat) {
                        $cat->where('nombre', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhere('descripcion_libre', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->orderByRaw("FIELD(estatus, 'PENDIENTE', 'SURTIDA_INVENTARIO', 'PENDIENTE_COMPRA') DESC")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $contadores = [
            'pendientes' => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE)->count(),
            'surtidas' => SolicitudPieza::where('estatus', SolicitudPieza::SURTIDA_INVENTARIO)->count(),
            'pendientes_compra' => SolicitudPieza::where('estatus', SolicitudPieza::PENDIENTE_COMPRA)->count(),
            'compradas' => SolicitudPieza::where('estatus', SolicitudPieza::COMPRADA)->count(),
            'confirmadas' => SolicitudPieza::where('estatus', SolicitudPieza::CONFIRMADA)->count(),
            'canceladas' => SolicitudPieza::where('estatus', SolicitudPieza::CANCELADA)->count(),
        ];

        return view('livewire.inventario.gestion-solicitudes-piezas', [
            'solicitudes' => $solicitudes,
            'contadores' => $contadores,
        ])->layout('layouts.app');
    }

    public function abrirModalSurtir($solicitudId)
    {
        $this->solicitudSeleccionada = SolicitudPieza::with([
            'catalogoPieza',
            'asignacionEquipo.equipo',
            'equipo',
            'solicitadoPor'
        ])->findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerGestionada()) {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return;
        }

        // Buscar piezas disponibles del mismo tipo (si hay catálogo)
        if ($this->solicitudSeleccionada->catalogo_pieza_id) {
            $this->piezasDisponibles = InventarioPieza::where('catalogo_pieza_id', $this->solicitudSeleccionada->catalogo_pieza_id)
                ->where('cantidad_disponible', '>', 0)
                ->with(['almacen', 'compraItem.compra.proveedor', 'equipoOrigen'])
                ->get();

            if ($this->piezasDisponibles->isEmpty()) {
                session()->flash('warning', 'No hay piezas disponibles en stock. Marca como pendiente de compra.');
            } else {
                $this->piezaSeleccionada = $this->piezasDisponibles->first()->id;
            }
        } else {
            $this->piezasDisponibles = collect();
            session()->flash('info', 'Solicitud con descripción libre. Busca la pieza manualmente o marca como pendiente de compra.');
        }

        // Cargar técnicos activos para reasignar
        $this->tecnicos = User::whereHas('role', fn($q) => $q->whereIn('slug', ['tecnico', 'lider']))
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno'])
            ->toArray();

        // Preseleccionar el técnico que hizo la solicitud
        $this->tecnicoReasignadoId = $this->solicitudSeleccionada->solicitado_por_id;

        $this->notasRespuesta = '';
        $this->modalSurtir = true;
    }

    public function surtirDeInventario()
    {
        $this->validate([
            'piezaSeleccionada'   => 'required|exists:inventario_piezas,id',
            'notasRespuesta'      => 'nullable|string|max:500',
            'tecnicoReasignadoId' => 'required|exists:users,id',
        ], [
            'tecnicoReasignadoId.required' => 'Debes seleccionar a quién se reasigna la instalación.',
        ]);

        try {
            $this->solicitudSeleccionada->surtirDeInventario(
                $this->piezaSeleccionada,
                auth()->id(),
                $this->notasRespuesta,
                $this->tecnicoReasignadoId
            );

            session()->flash('success', 'Pieza reasignada correctamente. El técnico la verá en su lista de trabajo.');

            $this->cerrarModales();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al reasignar: ' . $e->getMessage());
        }
    }

    public function abrirModalCompra($solicitudId)
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerGestionada()) {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return;
        }

        $this->notasRespuesta = '';
        $this->modalCompra = true;
    }

    public function marcarPendienteCompra()
    {
        $this->validate([
            'notasRespuesta' => 'nullable|string|max:500',
        ]);

        try {
            $this->solicitudSeleccionada->marcarPendienteCompra(
                auth()->id(),
                $this->notasRespuesta
            );

            session()->flash('success', 'Solicitud marcada como pendiente de compra.');
            
            $this->cerrarModales();

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function abrirModalCancelar($solicitudId)
    {
        $this->solicitudSeleccionada = SolicitudPieza::findOrFail($solicitudId);

        if (!$this->solicitudSeleccionada->puedeSerGestionada()) {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return;
        }

        $this->motivoCancelacion = '';
        $this->modalCancelar = true;
    }

    public function cancelarSolicitud()
    {
        $this->validate([
            'motivoCancelacion' => 'required|string|max:500',
        ]);

        try {
            $this->solicitudSeleccionada->cancelar(
                auth()->id(),
                $this->motivoCancelacion
            );

            session()->flash('success', 'Solicitud cancelada.');
            
            $this->cerrarModales();

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cambiarFiltro($estatus)
    {
        $this->filtroEstatus = $estatus;
        $this->resetPage();
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    private function cerrarModales()
    {
        $this->modalSurtir = false;
        $this->modalCompra = false;
        $this->modalCancelar = false;
        $this->reset([
            'solicitudSeleccionada',
            'piezaSeleccionada',
            'notasRespuesta',
            'motivoCancelacion',
            'piezasDisponibles',
            'tecnicoReasignadoId',
            'tecnicos',
        ]);
    }
}
