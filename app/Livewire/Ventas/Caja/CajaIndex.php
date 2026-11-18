<?php

namespace App\Livewire\Ventas\Caja;

use App\Models\CorteCaja;
use App\Models\Venta;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Cortes de Caja'])]
class CajaIndex extends Component
{
    public $corteActual = null;
    
    // Abrir Caja
    public $fondoInicial = 0;
    public $modalAbrir = false;

    // Cerrar Caja
    public $modalCerrar = false;
    public $declaradoEfectivo = 0;
    
    public function mount()
    {
        $this->cargarCorteActual();
    }

    public function cargarCorteActual()
    {
        $this->corteActual = CorteCaja::where('user_id', auth()->id())
            ->where('estatus', 'ABIERTA')
            ->first();
            
        if ($this->corteActual) {
            // Recalcular ventas
            $ventas = Venta::where('corte_caja_id', $this->corteActual->id)
                           ->where('estatus', 'COMPLETADA')
                           ->get();
            
            $this->corteActual->total_ventas_efectivo = $ventas->where('metodo_pago', 'EFECTIVO')->sum('total');
            $this->corteActual->total_ventas_tarjeta = $ventas->where('metodo_pago', 'TARJETA')->sum('total');
            $this->corteActual->total_ventas_transferencia = $ventas->where('metodo_pago', 'TRANSFERENCIA')->sum('total');
            
            $this->corteActual->total_calculado = $this->corteActual->fondo_inicial + $this->corteActual->total_ventas_efectivo;
            $this->corteActual->save();
        }
    }

    public function abrirCaja()
    {
        $this->validate([
            'fondoInicial' => 'required|numeric|min:0'
        ]);

        CorteCaja::create([
            'user_id' => auth()->id(),
            'sucursal_id' => auth()->user()->sucursal_id ?? 1,
            'fondo_inicial' => $this->fondoInicial,
            'estatus' => 'ABIERTA',
        ]);

        $this->modalAbrir = false;
        $this->fondoInicial = 0;
        $this->cargarCorteActual();
        $this->dispatch('toast', type: 'success', message: 'Caja abierta correctamente.');
    }

    public function prepararCierre()
    {
        $this->cargarCorteActual();
        $this->declaradoEfectivo = $this->corteActual->total_calculado;
        $this->modalCerrar = true;
    }

    public function cerrarCaja()
    {
        $this->validate([
            'declaradoEfectivo' => 'required|numeric|min:0'
        ]);

        $diferencia = $this->declaradoEfectivo - $this->corteActual->total_calculado;

        $this->corteActual->update([
            'total_declarado' => $this->declaradoEfectivo,
            'diferencia' => $diferencia,
            'estatus' => 'CERRADA',
            'fecha_cierre' => now(),
        ]);

        $this->modalCerrar = false;
        $this->corteActual = null;
        $this->dispatch('toast', type: 'success', message: 'Caja cerrada correctamente.');
    }

    public function render()
    {
        $cortesPasados = CorteCaja::where('user_id', auth()->id())
            ->where('estatus', 'CERRADA')
            ->orderBy('fecha_cierre', 'desc')
            ->take(10)
            ->get();

        return view('livewire.ventas.caja.caja-index', [
            'cortesPasados' => $cortesPasados
        ]);
    }
}
