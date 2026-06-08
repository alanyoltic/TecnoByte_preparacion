<?php

namespace App\Livewire\Preparacion\Lotes;

use App\Models\Almacen;
use App\Models\Equipo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Proveedor;
use App\Models\ClasificacionPuntos;
use App\Models\LoteModeloRecibido;
use App\Models\CatalogoEquipo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\EquipoMovimientoService;

#[Layout('layouts.app', ['pageTitle' => 'Registrar Lote'])]
class RegistrarLote extends Component
{
    public $nombre_lote;
    public $proveedor_id;
    public $fecha_llegada;
    public $modelos = [];
    public $marcas  = [];

    public bool $modalSeries  = false;
    public int  $serialesIndex = -1;

    public function mount()
    {
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.gestion'), 403);

        $this->fecha_llegada = now()->toDateString();

        $this->marcas = CatalogoEquipo::select('marca')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca')
            ->toArray();

        $this->modelos = [
            [
                'temp_marca'              => '',
                'modelos_filtrados'       => [],
                'catalogo_equipo_id'      => null,
                'cantidad_recibida'       => 1,
                'valor_unitario'          => '',
                'clasificacion_puntos_id' => null,
                'numeros_serie'           => [],
            ],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'temp_marca'              => '',
            'modelos_filtrados'       => [],
            'catalogo_equipo_id'      => null,
            'cantidad_recibida'       => 1,
            'valor_unitario'          => '',
            'clasificacion_puntos_id' => null,
            'numeros_serie'           => [],
        ];
    }

    public function updatedModelos($value, $name): void
    {
        $parts = explode('.', $name);
        $index = (int) $parts[0];
        $field = $parts[1] ?? '';

        if ($field === 'temp_marca') {
            $this->modelos[$index]['catalogo_equipo_id'] = null; 
            
            if ($value) {
                $this->modelos[$index]['modelos_filtrados'] = CatalogoEquipo::where('marca', $value)
                    ->orderBy('modelo')
                    ->get(['id', 'modelo'])
                    ->toArray();
            } else {
                $this->modelos[$index]['modelos_filtrados'] = [];
            }
        }
    }

    public function removeModeloRow($index)
    {
        unset($this->modelos[$index]);
        $this->modelos = array_values($this->modelos);

        if (count($this->modelos) === 0) {
            $this->addModeloRow();
        }
    }

    // ── Modal de series ───────────────────────────────────────────────────

    public function abrirModalSeries(int $index): void
    {
        $this->serialesIndex = $index;
        if (empty($this->modelos[$index]['numeros_serie'])) {
            $this->modelos[$index]['numeros_serie'] = [''];
        }
        $this->modalSeries = true;
    }

    public function cerrarModalSeries(): void
    {
        $this->modalSeries   = false;
        $this->serialesIndex = -1;
    }

    public function agregarSerieModal(): void
    {
        if ($this->serialesIndex < 0) return;
        $this->modelos[$this->serialesIndex]['numeros_serie'][] = '';
    }

    public function quitarSerieModal(int $si): void
    {
        $idx = $this->serialesIndex;
        if ($idx < 0 || !isset($this->modelos[$idx]['numeros_serie'][$si])) return;
        array_splice($this->modelos[$idx]['numeros_serie'], $si, 1);
        $this->modelos[$idx]['numeros_serie'] = array_values($this->modelos[$idx]['numeros_serie']);
        if (empty($this->modelos[$idx]['numeros_serie'])) {
            $this->modelos[$idx]['numeros_serie'] = [''];
        }
    }

    // ── Guardar ───────────────────────────────────────────────────────────

    public function guardar()
    {
        $this->validate([
            'nombre_lote'   => 'required|string|max:255',
            'proveedor_id'  => 'required|exists:proveedores,id',
            'fecha_llegada' => 'nullable|date',

            'modelos'                              => 'required|array|min:1',
            'modelos.*.catalogo_equipo_id'         => 'required|exists:catalogo_equipos,id',
            'modelos.*.cantidad_recibida'          => 'required|integer|min:1',
            'modelos.*.valor_unitario'             => 'nullable|numeric|min:0',
            'modelos.*.clasificacion_puntos_id'    => 'nullable|exists:clasificaciones_puntos,id',
        ], [
            'modelos.required' => 'Debes agregar al menos un modelo al lote.',
            'modelos.*.catalogo_equipo_id.required' => 'Debes seleccionar un equipo del catálogo oficial.',
        ]);

        DB::transaction(function () {
            $loteId = DB::table('lotes')->insertGetId([
                'nombre_lote'   => $this->nombre_lote,
                'proveedor_id'  => $this->proveedor_id,
                'fecha_llegada' => $this->fecha_llegada ?: null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($this->modelos as $m) {
                
                $catItem = CatalogoEquipo::findOrFail($m['catalogo_equipo_id']);

                $loteModelo = LoteModeloRecibido::create([
                    'lote_id'                 => $loteId,
                    'catalogo_equipo_id'      => $catItem->id,
                    'marca'                   => $catItem->marca,
                    'modelo'                  => $catItem->modelo,
                    'cantidad_recibida'       => $m['cantidad_recibida'],
                    'valor_unitario'          => is_numeric($m['valor_unitario'] ?? '') ? (float) $m['valor_unitario'] : null,
                    'clasificacion_puntos_id' => $m['clasificacion_puntos_id'] ?: null,
                ]);

                // Pre-registrar equipos con número de serie
                $seriesTrimmed = array_values(array_filter(
                    array_map('trim', $m['numeros_serie'] ?? []), fn ($s) => $s !== ''
                ));

                foreach ($seriesTrimmed as $serie) {
                    if (Equipo::where('numero_serie', $serie)->exists()) {
                        throw ValidationException::withMessages([
                            'modelos' => "El número de serie '{$serie}' ya existe en el sistema.",
                        ]);
                    }
                }

                $almacenPreparacion = Almacen::find(Almacen::PREPARACION);

                foreach ($seriesTrimmed as $serie) {
                    $equipo = Equipo::create([
                        'numero_serie'           => $serie,
                        'lote_modelo_id'         => $loteModelo->id,
                        'catalogo_equipo_id'     => $catItem->id,
                        'clasificacion_puntos_id' => $loteModelo->clasificacion_puntos_id,
                        'marca'                  => $catItem->marca,
                        'modelo'                 => $catItem->modelo,
                        'estatus_ciclo'          => 'PREPARACION',
                        'estatus_area'           => 'SIN_ASIGNAR',
                        'registrado_por_user_id' => Auth::id(),
                        'proveedor_id'           => $this->proveedor_id,
                        'almacen_id'             => Almacen::PREPARACION,
                        'sucursal_id'            => Auth::user()->sucursal_id ?? 1,
                    ]);

                    if ($almacenPreparacion) {
                        app(EquipoMovimientoService::class)->abrirEstanciaInicial(
                            $equipo,
                            $almacenPreparacion,
                            'ALTA_LOTE',
                            'Alta inicial desde registro de lote'
                        );
                    }
                }
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Lote y modelos registrados correctamente.');

        $this->reset(['nombre_lote', 'proveedor_id', 'modelos']);
        $this->fecha_llegada = now()->toDateString();
        $this->modelos = [
            [
                'catalogo_equipo_id'      => null,
                'cantidad_recibida'       => 1,
                'valor_unitario'          => '',
                'clasificacion_puntos_id' => null,
                'numeros_serie'           => [],
            ],
        ];
    }

    public function render()
    {
        $proveedores     = Proveedor::orderBy('nombre_empresa')->get();
        $clasificaciones = ClasificacionPuntos::where('activo', true)->orderBy('clave')->get();
        $catalogo        = CatalogoEquipo::where('activo', true)->orderBy('marca')->orderBy('modelo')->get();

        return view('livewire.preparacion.lotes.registrar-lote', [
            'proveedores'     => $proveedores,
            'clasificaciones' => $clasificaciones,
            'catalogo'        => $catalogo,
        ]);
    }

}
