<?php

namespace App\Livewire\Preparacion\Inventario;

use App\Models\Consumible;
use App\Models\Equipo;
use App\Models\InventarioConsumible;
use App\Models\Transferencia;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Nueva transferencia'])]
class TransferenciasCrear extends Component
{
    public $sucursal_origen_id = '';

    public $almacen_origen_id = '';

    public $sucursal_destino_id = '';

    public $almacen_destino_id = '';

    public bool $puedeCambiarSucursales = false;

    public string $serialBusqueda = '';

    public array $equiposAgregados = [];

    public string $errorSerial = '';

    public array $consumiblesAgregados = [];

    public string $consumibleSeleccionado = '';

    public int $consumibleCantidad = 1;

    public string $errorConsumible = '';

    public string $observaciones = '';

    public function mount(): void
    {
        $this->autorizarAcceso();

        $user = auth()->user();
        $this->puedeCambiarSucursales = $user->puedeCambiarSucursales();

        // Inicializar sucursal origen y destino con la sucursal del usuario
        if ($user->sucursal_id) {
            $this->sucursal_origen_id = (string) $user->sucursal_id;
            $this->sucursal_destino_id = (string) $user->sucursal_id;
        } else {
            // Si no tiene sucursal fija (ej. CEO o Gerente matriz), tomar la primera sucursal permitida
            $primerSucursal = $user->sucursalesPermitidas()->first();
            if ($primerSucursal) {
                $this->sucursal_origen_id = (string) $primerSucursal->id;
                $this->sucursal_destino_id = (string) $primerSucursal->id;
            }
        }
    }

    protected function autorizarAcceso(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->puedeCrearTransferencias(), 403, 'No tienes permiso para crear transferencias.');
    }

    public function updatedSucursalOrigenId(): void
    {
        $this->almacen_origen_id = '';
        $this->updatedAlmacenOrigenId();
    }

    public function updatedSucursalDestinoId(): void
    {
        $this->almacen_destino_id = '';
    }

    public function updatedAlmacenOrigenId(): void
    {
        $this->equiposAgregados = [];
        $this->consumiblesAgregados = [];
        $this->serialBusqueda = '';
        $this->consumibleSeleccionado = '';
        $this->errorSerial = '';
        $this->errorConsumible = '';
    }

    public function getConsumiblesDisponiblesProperty()
    {
        if (! $this->almacen_origen_id) {
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

        if (! $busqueda) {
            $this->errorSerial = 'Ingresa un numero de serie o ID.';

            return;
        }

        if (! $this->almacen_origen_id) {
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

        if (! $equipo) {
            $this->errorSerial = "No se encontro [{$busqueda}] en este almacen.";

            return;
        }

        $motivoNoApto = '';
        if (! $equipo->puedeTransferirse($motivoNoApto)) {
            $this->errorSerial = "No se puede transferir [{$equipo->numero_serie}]: {$motivoNoApto}";

            return;
        }

        if (collect($this->equiposAgregados)->contains('id', $equipo->id)) {
            $this->errorSerial = "El equipo [{$equipo->numero_serie}] ya fue agregado.";

            return;
        }

        $this->equiposAgregados[] = [
            'id' => $equipo->id,
            'serie' => $equipo->numero_serie,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'descripcion' => trim(implode(' / ', array_filter([
                $equipo->ram_total,
                $equipo->almacenamiento_principal_capacidad,
                $equipo->almacenamiento_principal_tipo,
            ]))),
        ];

        $this->serialBusqueda = '';
    }

    public function agregarEquipoPorId(int $id): void
    {
        $this->errorSerial = '';

        if (! $this->almacen_origen_id) {
            $this->errorSerial = 'Selecciona primero el almacén origen.';

            return;
        }

        $equipo = Equipo::where('almacen_id', $this->almacen_origen_id)
            ->where('id', $id)
            ->first();

        if (! $equipo) {
            $this->errorSerial = 'El equipo ya no se encuentra en este almacén.';

            return;
        }

        $motivoNoApto = '';
        if (! $equipo->puedeTransferirse($motivoNoApto)) {
            $this->errorSerial = "No se puede transferir [{$equipo->numero_serie}]: {$motivoNoApto}";

            return;
        }

        if (collect($this->equiposAgregados)->contains('id', $equipo->id)) {
            $this->errorSerial = "El equipo [{$equipo->numero_serie}] ya fue agregado.";

            return;
        }

        $this->equiposAgregados[] = [
            'id' => $equipo->id,
            'serie' => $equipo->numero_serie,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'descripcion' => trim(implode(' / ', array_filter([
                $equipo->ram_total,
                $equipo->almacenamiento_principal_capacidad,
                $equipo->almacenamiento_principal_tipo,
            ]))),
        ];

        $this->serialBusqueda = '';
    }

    public function getEquiposSugeridosProperty()
    {
        $busqueda = trim($this->serialBusqueda);

        if (strlen($busqueda) < 2 || ! $this->almacen_origen_id) {
            return collect();
        }

        $idsAgregados = array_column($this->equiposAgregados, 'id');

        return Equipo::where('almacen_id', $this->almacen_origen_id)
            ->whereNotIn('id', $idsAgregados)
            ->where('estatus_ciclo', '!=', Equipo::CICLO_VENDIDO)
            ->where(function ($q) use ($busqueda) {
                $q->where('numero_serie', 'like', "%{$busqueda}%")
                    ->orWhere('marca', 'like', "%{$busqueda}%")
                    ->orWhere('modelo', 'like', "%{$busqueda}%");
                if (is_numeric($busqueda)) {
                    $q->orWhere('id', (int) $busqueda);
                }
            })
            ->limit(10)
            ->get();
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

        if (! $this->consumibleSeleccionado) {
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

        if (! $inventario || $inventario->cantidad < $this->consumibleCantidad) {
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
            'nombre' => $inventario->consumible->nombre,
            'cantidad' => $this->consumibleCantidad,
            'stock' => $inventario->cantidad,
        ];

        $this->consumibleSeleccionado = '';
        $this->consumibleCantidad = 1;
    }

    public function quitarConsumible(int $index): void
    {
        unset($this->consumiblesAgregados[$index]);
        $this->consumiblesAgregados = array_values($this->consumiblesAgregados);
    }

    // =========================================================================
    // GUARDAR
    // =========================================================================
    public function guardar(bool $enviarInmediato = false, ?\App\Services\TransferenciaService $service = null): void
    {
        $this->autorizarAcceso();

        $this->validate([
            'almacen_origen_id' => 'required',
            'almacen_destino_id' => 'required|different:almacen_origen_id',
        ]);

        if (empty($this->equiposAgregados) && empty($this->consumiblesAgregados)) {
            $this->addError('items', 'Debes agregar al menos un equipo o consumible.');

            return;
        }

        try {
            $transferencia = DB::transaction(function () use ($enviarInmediato, $service) {
                $transferencia = Transferencia::create([
                    'almacen_origen_id' => $this->almacen_origen_id,
                    'almacen_destino_id' => $this->almacen_destino_id,
                    'created_by' => auth()->id(),
                    'estatus' => 'BORRADOR',
                    'observaciones' => $this->observaciones ?: null,
                ]);

                foreach ($this->equiposAgregados as $e) {
                    $transferencia->detalles()->create([
                        'movible_id' => $e['id'],
                        'movible_type' => Equipo::class,
                        'cantidad' => 1,
                    ]);
                }

                foreach ($this->consumiblesAgregados as $c) {
                    $transferencia->detalles()->create([
                        'movible_id' => $c['consumible_id'],
                        'movible_type' => Consumible::class,
                        'cantidad' => $c['cantidad'],
                    ]);
                }

                if ($enviarInmediato) {
                    $service = $service ?: app(\App\Services\TransferenciaService::class);
                    $service->enviar($transferencia);
                }

                return $transferencia;
            });

            if ($enviarInmediato) {
                session()->flash('success', "Transferencia #{$transferencia->id} creada y enviada correctamente.");
            } else {
                session()->flash('success', "Transferencia #{$transferencia->id} guardada como borrador.");
            }

            $rutaDestino = auth()->user()->rutaListadoTransferencias();

            $this->redirect($rutaDestino, navigate: true);

        } catch (\Exception $e) {
            $this->addError('general', 'Error al procesar transferencia: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // RENDER
    // =========================================================================
    public function render()
    {
        $user = auth()->user();

        $sucursales = $user->sucursalesPermitidas();

        $almacenesOrigen = $user->almacenesPermitidosOrigen(
            $this->sucursal_origen_id ? (int) $this->sucursal_origen_id : null
        );

        $almacenesDestino = $user->almacenesPermitidosDestino(
            $this->sucursal_destino_id ? (int) $this->sucursal_destino_id : null
        )->where('id', '!=', $this->almacen_origen_id ?: 0);

        return view('livewire.preparacion.inventario.transferencias-crear', [
            'puedeCambiarSucursales' => $this->puedeCambiarSucursales,
            'sucursales' => $sucursales,
            'almacenesOrigen' => $almacenesOrigen,
            'almacenesDestino' => $almacenesDestino,
            'equiposSugeridos' => $this->equiposSugeridos,
            'equiposAgregados' => $this->equiposAgregados,
            'consumiblesAgregados' => $this->consumiblesAgregados,
            'almacen_origen_id' => $this->almacen_origen_id,
            'almacen_destino_id' => $this->almacen_destino_id,
            'sucursal_origen_id' => $this->sucursal_origen_id,
            'sucursal_destino_id' => $this->sucursal_destino_id,
            'serialBusqueda' => $this->serialBusqueda,
            'errorSerial' => $this->errorSerial,
            'errorConsumible' => $this->errorConsumible,
        ]);
    }
}
