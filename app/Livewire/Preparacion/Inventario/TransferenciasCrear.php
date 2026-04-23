<?php

namespace App\Livewire\Preparacion\Inventario;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Almacen;
use App\Models\Transferencia;
use App\Models\Equipo;
use App\Models\Consumible;
use App\Models\InventarioConsumible;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app', ['pageTitle' => 'Nueva transferencia'])]
class TransferenciasCrear extends Component
{
    public $almacenesOrigen  = [];
    public $almacen_origen_id  = '';
    public $almacen_destino_id = '';

    public string $serialBusqueda   = '';
    public array  $equiposAgregados = [];
    public string $errorSerial      = '';

    public array  $consumiblesAgregados   = [];
    public string $consumibleSeleccionado = '';
    public int    $consumibleCantidad     = 1;
    public string $errorConsumible        = '';

    public string $observaciones = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.transferencias.crear'), 403);

        $this->almacenesOrigen = auth()->user()->almacenesPermitidosOrigen();
    }

    public function updatedAlmacenOrigenId(): void
    {
        $this->equiposAgregados       = [];
        $this->consumiblesAgregados   = [];
        $this->serialBusqueda         = '';
        $this->consumibleSeleccionado = '';
        $this->errorSerial            = '';
        $this->errorConsumible        = '';
    }

    public function getConsumiblesDisponiblesProperty()
    {
        if (!$this->almacen_origen_id) {
            return collect();
        }

        return InventarioConsumible::with('consumible')
            ->where('almacen_id', $this->almacen_origen_id)
            ->where('cantidad', '>', 0)
            ->get();
    }

    // =========================================================================
    // AGREGAR EQUIPO POR ID O NUMERO DE SERIE
    // =========================================================================
    public function agregarSerial(): void
    {
        $this->errorSerial = '';
        $busqueda = trim($this->serialBusqueda);

        if (!$busqueda) {
            $this->errorSerial = 'Ingresa un numero de serie o ID.';
            return;
        }

        if (!$this->almacen_origen_id) {
            $this->errorSerial = 'Selecciona primero el almacen origen.';
            return;
        }

        $equipo = Equipo::where('almacen_id', $this->almacen_origen_id)
            ->where(function ($q) use ($busqueda) {
                $q->whereRaw('UPPER(numero_serie) = ?', [strtoupper($busqueda)]);
                if (is_numeric($busqueda)) {
                    $q->orWhere('id', (int) $busqueda);
                }
            })
            ->first();

        if (!$equipo) {
            $this->errorSerial = "No se encontro [{$busqueda}] en este almacen.";
            return;
        }

        if (collect($this->equiposAgregados)->contains('id', $equipo->id)) {
            $this->errorSerial = "El equipo [{$equipo->numero_serie}] ya fue agregado.";
            return;
        }

        $this->equiposAgregados[] = [
            'id'          => $equipo->id,
            'serie'       => $equipo->numero_serie,
            'marca'       => $equipo->marca,
            'modelo'      => $equipo->modelo,
            'descripcion' => trim(implode(' / ', array_filter([
                $equipo->ram_total,
                $equipo->almacenamiento_principal_capacidad,
                $equipo->almacenamiento_principal_tipo,
            ]))),
        ];

        $this->serialBusqueda = '';
    }

    public function quitarEquipo(int $index): void
    {
        unset($this->equiposAgregados[$index]);
        $this->equiposAgregados = array_values($this->equiposAgregados);
    }

    // =========================================================================
    // CONSUMIBLES
    // =========================================================================
    public function agregarConsumible(): void
    {
        $this->errorConsumible = '';

        if (!$this->consumibleSeleccionado) {
            $this->errorConsumible = 'Selecciona un consumible.';
            return;
        }

        if ($this->consumibleCantidad < 1) {
            $this->errorConsumible = 'La cantidad debe ser al menos 1.';
            return;
        }

        $inventario = InventarioConsumible::with('consumible')
            ->where('consumible_id', $this->consumibleSeleccionado)
            ->where('almacen_id', $this->almacen_origen_id)
            ->first();

        if (!$inventario || $inventario->cantidad < $this->consumibleCantidad) {
            $disponible = $inventario->cantidad ?? 0;
            $this->errorConsumible = "Stock insuficiente. Disponible: {$disponible}";
            return;
        }

        if (collect($this->consumiblesAgregados)->contains('consumible_id', (int) $this->consumibleSeleccionado)) {
            $this->errorConsumible = 'Este consumible ya fue agregado. Edita la cantidad directamente.';
            return;
        }

        $this->consumiblesAgregados[] = [
            'consumible_id' => (int) $this->consumibleSeleccionado,
            'nombre'        => $inventario->consumible->nombre,
            'cantidad'      => $this->consumibleCantidad,
            'stock'         => $inventario->cantidad,
        ];

        $this->consumibleSeleccionado = '';
        $this->consumibleCantidad     = 1;
    }

    public function quitarConsumible(int $index): void
    {
        unset($this->consumiblesAgregados[$index]);
        $this->consumiblesAgregados = array_values($this->consumiblesAgregados);
    }

    // =========================================================================
    // GUARDAR
    // =========================================================================
    public function guardar(): void
    {
        abort_unless(auth()->user()?->tienePermiso('prep.transferencias.crear'), 403);

        $this->validate([
            'almacen_origen_id'  => 'required',
            'almacen_destino_id' => 'required|different:almacen_origen_id',
        ]);

        if (empty($this->equiposAgregados) && empty($this->consumiblesAgregados)) {
            $this->addError('items', 'Debes agregar al menos un equipo o consumible.');
            return;
        }

        DB::transaction(function () {
            $transferencia = Transferencia::create([
                'almacen_origen_id'  => $this->almacen_origen_id,
                'almacen_destino_id' => $this->almacen_destino_id,
                'created_by'         => auth()->id(),
                'estatus'            => 'BORRADOR',
                'observaciones'      => $this->observaciones ?: null,
            ]);

            foreach ($this->equiposAgregados as $e) {
                $transferencia->detalles()->create([
                    'movible_id'   => $e['id'],
                    'movible_type' => Equipo::class,
                    'cantidad'     => 1,
                ]);
            }

            foreach ($this->consumiblesAgregados as $c) {
                $transferencia->detalles()->create([
                    'movible_id'   => $c['consumible_id'],
                    'movible_type' => Consumible::class,
                    'cantidad'     => $c['cantidad'],
                ]);
            }
        });

        session()->flash('success', 'Transferencia guardada como borrador.');
        $this->redirect(route('inventario.transferencias'), navigate: true);
    }

    // =========================================================================
    // RENDER
    // =========================================================================
    public function render()
    {
        $user = auth()->user();

        return view('livewire.preparacion.inventario.transferencias-crear', [
            'almacenesDestino' => $user->almacenesPermitidosDestino()
                ->where('id', '!=', $this->almacen_origen_id ?: 0),
            'errorSerial'      => $this->errorSerial,
            'errorConsumible'  => $this->errorConsumible,
        ]);
    }
}