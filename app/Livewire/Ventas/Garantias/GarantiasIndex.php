<?php

namespace App\Livewire\Ventas\Garantias;

use App\Models\Equipo;
use App\Models\GarantiaCliente;
use App\Models\DespachoVentas;
use App\Models\DespachoVentasEquipo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Garantías de Clientes'])]
class GarantiasIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $estatusFiltro = '';
    
    // Modal Nueva Garantia
    public bool $modalNueva = false;
    public string $serialBuscado = '';
    public ?Equipo $equipoEncontrado = null;
    public ?\App\Models\Venta $ventaEncontrada = null;
    
    // Formulario de Recepcion
    public string $fallaReportada = '';
    public string $condicionesRecepcion = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function buscarPorSerie()
    {
        $this->reset(['equipoEncontrado', 'ventaEncontrada']);
        $this->validate(['serialBuscado' => 'required|string']);

        $equipo = Equipo::where('numero_serie', $this->serialBuscado)->first();

        if (!$equipo) {
            $this->addError('serialBuscado', 'Equipo no encontrado en el sistema.');
            return;
        }

        // Buscar si el equipo tiene una venta activa
        // Asumiendo que VentaDetalle almacena el equipo vendido o que Venta tiene la relacion.
        // Si no lo tenemos mapeado directamente en una relacion, buscamos en venta_detalles
        $detalle = \App\Models\VentaDetalle::where('equipo_id', $equipo->id)
                    ->whereHas('venta', function($q) {
                        $q->where('estatus', 'COMPLETADA');
                    })
                    ->latest()->first();

        if (!$detalle) {
            $this->addError('serialBuscado', 'Este equipo no cuenta con un registro de venta completada.');
            return;
        }

        // Verificar si ya tiene garantía activa
        $garantiaActiva = GarantiaCliente::where('equipo_id', $equipo->id)
                            ->whereNotIn('estatus', ['ENTREGADO', 'RECHAZADO'])
                            ->exists();
                            
        if ($garantiaActiva) {
            $this->addError('serialBuscado', 'Este equipo ya tiene un proceso de garantía en curso.');
            return;
        }

        $this->equipoEncontrado = $equipo;
        $this->ventaEncontrada = $detalle->venta;
        $this->fallaReportada = '';
        $this->condicionesRecepcion = '';
    }

    public function procesarRecepcionGarantia()
    {
        $this->validate([
            'fallaReportada' => 'required|string|max:1000',
            'condicionesRecepcion' => 'nullable|string|max:1000',
        ]);

        if (!$this->equipoEncontrado || !$this->ventaEncontrada) {
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Crear el registro de la Garantía
            $garantia = GarantiaCliente::create([
                'venta_id' => $this->ventaEncontrada->id,
                'equipo_id' => $this->equipoEncontrado->id,
                'cliente_id' => $this->ventaEncontrada->cliente_id,
                'reportado_por_id' => auth()->id(),
                'almacen_origen_id' => auth()->user()->almacen_id ?? 1, // Fallback temporal
                'estatus' => 'PENDIENTE_CEDIS',
                'falla_reportada' => $this->fallaReportada,
                'condiciones_recepcion' => $this->condicionesRecepcion,
            ]);

            // 2. Cambiar estatus del equipo a Garantia
            $this->equipoEncontrado->update([
                'ciclo_actual' => 'GARANTIA_CLIENTE',
                'estatus' => 'EN_REVISION'
            ]);

            // 3. Crear el Despacho de Retorno a CEDIS
            $despacho = DespachoVentas::create([
                'origen_almacen_id' => auth()->user()->almacen_id ?? 1,
                'destino_almacen_id' => 1, // Asumiendo CEDIS = 1
                'creado_por_id' => auth()->id(),
                'estatus' => 'EN_TRANSITO', // Sale inmediatamente de tienda
                'tipo' => 'RETORNO_GARANTIA',
                'observaciones' => 'Retorno por Garantía Folio Venta: ' . $this->ventaEncontrada->folio,
            ]);

            DespachoVentasEquipo::create([
                'despacho_ventas_id' => $despacho->id,
                'equipo_id' => $this->equipoEncontrado->id,
                'estatus' => 'PENDIENTE'
            ]);

            // Vincular despacho a la garantía
            $garantia->update(['despacho_retorno_id' => $despacho->id]);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Garantía iniciada y Despacho generado correctamente.');
            $this->modalNueva = false;
            $this->reset(['serialBuscado', 'equipoEncontrado', 'ventaEncontrada']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = GarantiaCliente::with(['equipo', 'cliente', 'venta'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->whereHas('equipo', function ($q) {
                $q->where('numero_serie', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->estatusFiltro) {
            $query->where('estatus', $this->estatusFiltro);
        }

        $garantias = $query->paginate(15);

        return view('livewire.ventas.garantias.garantias-index', [
            'garantias' => $garantias
        ]);
    }
}
