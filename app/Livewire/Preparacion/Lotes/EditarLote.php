<?php

namespace App\Livewire\Preparacion\Lotes;

use App\Models\Almacen;
use App\Models\CatalogoEquipo;
use App\Models\ClasificacionPuntos;
use App\Models\Equipo;
use App\Models\Lote;
use App\Models\LoteModeloRecibido;
use App\Models\Proveedor;
use App\Services\EquipoMovimientoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Editar lote'])]
class EditarLote extends Component
{
    public int $loteId;

    public ?Lote $lote = null;

    public $nombre_lote;

    public $proveedor_id;

    public $fecha_llegada;

    public $proveedores = [];

    public $catalogo = [];

    public array $modelos = [];

    public array $deleteModeloIds = [];

    public bool $modalSeries = false;

    public int $serialesIndex = -1;

    public function mount(Lote $lote)
    {
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.gestion'), 403);

        $this->loteId = $lote->id;

        $this->proveedores = Proveedor::orderBy('abreviacion')->get();

        $this->lote = Lote::with(['modelosRecibidos' => function ($q) {
            $q->withCount('equipos')->orderBy('id');
        }])->findOrFail($this->loteId);

        $this->nombre_lote = $this->lote->nombre_lote;
        $this->proveedor_id = $this->lote->proveedor_id;
        $this->fecha_llegada = $this->lote->fecha_llegada;

        $this->modelos = $this->lote->modelosRecibidos->map(function ($m) {
            $tempMarca = $m->catalogoEquipo?->marca ?? '';
            $modelosFiltrados = $tempMarca ? CatalogoEquipo::where('marca', $tempMarca)->orderBy('modelo')->get(['id', 'modelo'])->toArray() : [];

            return [
                'id' => $m->id,
                'temp_marca' => $tempMarca,
                'catalogo_equipo_id' => $m->catalogo_equipo_id,
                'modelos_filtrados' => $modelosFiltrados,
                'cantidad_recibida' => (int) $m->cantidad_recibida,
                'valor_unitario' => $m->valor_unitario !== null ? (string) $m->valor_unitario : '',
                'clasificacion_puntos_id' => $m->clasificacion_puntos_id,
                'equipos_registrados' => (int) ($m->equipos_count ?? 0),
                'numeros_serie' => [],
            ];
        })->toArray();

        if (count($this->modelos) === 0) {
            $this->addModeloRow();
        }
    }

    protected function rules()
    {
        return [
            'nombre_lote' => ['required', 'string', 'max:255'],
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_llegada' => ['nullable', 'date'],

            'modelos' => ['required', 'array', 'min:1'],
            'modelos.*.catalogo_equipo_id' => ['required', 'exists:catalogo_equipos,id'],
            'modelos.*.cantidad_recibida' => ['required', 'integer', 'min:1'],
            'modelos.*.valor_unitario' => ['nullable', 'numeric', 'min:0'],
            'modelos.*.clasificacion_puntos_id' => ['nullable', 'exists:clasificaciones_puntos,id'],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'id' => null,
            'temp_marca' => '',
            'catalogo_equipo_id' => null,
            'modelos_filtrados' => [],
            'cantidad_recibida' => 1,
            'valor_unitario' => '',
            'clasificacion_puntos_id' => null,
            'equipos_registrados' => 0,
            'numeros_serie' => [],
        ];
    }

    public function removeModeloRow($index)
    {
        if (! isset($this->modelos[$index])) {
            return;
        }

        $row = $this->modelos[$index];

        if (! empty($row['id']) && ((int) ($row['equipos_registrados'] ?? 0) > 0)) {
            throw ValidationException::withMessages([
                'modelos' => 'No puedes eliminar un modelo que ya tiene equipos registrados.',
            ]);
        }

        if (! empty($row['id'])) {
            $this->deleteModeloIds[] = (int) $row['id'];
        }

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
        $this->modalSeries = false;
        $this->serialesIndex = -1;
    }

    public function agregarSerieModal(): void
    {
        if ($this->serialesIndex < 0) {
            return;
        }
        $this->modelos[$this->serialesIndex]['numeros_serie'][] = '';
    }

    public function quitarSerieModal(int $si): void
    {
        $idx = $this->serialesIndex;
        if ($idx < 0 || ! isset($this->modelos[$idx]['numeros_serie'][$si])) {
            return;
        }
        array_splice($this->modelos[$idx]['numeros_serie'], $si, 1);
        $this->modelos[$idx]['numeros_serie'] = array_values($this->modelos[$idx]['numeros_serie']);
        if (empty($this->modelos[$idx]['numeros_serie'])) {
            $this->modelos[$idx]['numeros_serie'] = [''];
        }
    }

    /** Series ya en DB para el modelo actualmente abierto (read-only en modal). */
    public function seriesExistentes(): array
    {
        if ($this->serialesIndex < 0) {
            return [];
        }
        $modeloId = $this->modelos[$this->serialesIndex]['id'] ?? null;
        if (! $modeloId) {
            return [];
        }

        return Equipo::where('lote_modelo_id', $modeloId)
            ->where('estatus_area', Equipo::AREA_SIN_ASIGNAR)
            ->pluck('numero_serie')
            ->toArray();
    }

    // ── Validaciones ──────────────────────────────────────────────────────

    private function validarCantidadesVsRegistrados()
    {
        foreach ($this->modelos as $i => $m) {
            $registrados = (int) ($m['equipos_registrados'] ?? 0);
            $cantidad = (int) ($m['cantidad_recibida'] ?? 0);

            if ($cantidad < $registrados) {
                throw ValidationException::withMessages([
                    "modelos.$i.cantidad_recibida" => "La cantidad no puede ser menor a los equipos ya registrados ($registrados).",
                ]);
            }
        }
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

            return;
        }

        if ($field !== 'cantidad_recibida') {
            return;
        }

        $registrados = (int) ($this->modelos[$index]['equipos_registrados'] ?? 0);
        $cantidad = (int) ($this->modelos[$index]['cantidad_recibida'] ?? 0);

        if ($cantidad < $registrados) {
            $this->modelos[$index]['cantidad_recibida'] = $registrados;
            $this->addError("modelos.$index.cantidad_recibida",
                "Mínimo permitido: $registrados (ya registrados)."
            );
            $this->dispatch('toast', type: 'error', message: "No puedes bajar de $registrados porque ya hay equipos registrados.");
        }
    }

    // ── Actualizar ────────────────────────────────────────────────────────

    public function actualizarLote()
    {
        $this->validate();
        $this->validarCantidadesVsRegistrados();

        DB::transaction(function () {

            $lote = Lote::findOrFail($this->loteId);

            $lote->update([
                'nombre_lote' => $this->nombre_lote,
                'proveedor_id' => $this->proveedor_id,
                'fecha_llegada' => $this->fecha_llegada,
            ]);

            if (! empty($this->deleteModeloIds)) {
                $modelosABorrar = LoteModeloRecibido::where('lote_id', $lote->id)
                    ->whereIn('id', $this->deleteModeloIds)->get();
                
                $traceService = app(\App\Services\EquipoTraceService::class);
                foreach ($modelosABorrar as $modRecibido) {
                    $equiposBorrados = $modRecibido->equipos()->onlyTrashed()->get();
                    foreach ($equiposBorrados as $equipo) {
                        $traceService->crearSnapshotEliminacion($equipo, 'Eliminación forzada por actualización de lote');
                        $equipo->gpus()->delete();
                        $equipo->baterias()->delete();
                        if ($equipo->monitor) {
                            $equipo->monitor()->delete();
                        }
                        \App\Models\EquipoAuditoria::where('equipo_id', $equipo->id)->delete();
                        $equipo->forceDelete();
                    }
                    $modRecibido->delete();
                }
            }

            foreach ($this->modelos as $m) {

                $clasificacionId = $m['clasificacion_puntos_id'] ?: null;
                $catalogoId = $m['catalogo_equipo_id'];

                $catItem = CatalogoEquipo::findOrFail($catalogoId);

                if (! empty($m['id'])) {
                    $registro = LoteModeloRecibido::where('lote_id', $lote->id)
                        ->where('id', (int) $m['id'])
                        ->firstOrFail();

                    $clasificacionCambio = $registro->clasificacion_puntos_id != $clasificacionId;

                    $registro->update([
                        'catalogo_equipo_id' => $catalogoId,
                        'marca' => $catItem->marca,
                        'modelo' => $catItem->modelo,
                        'cantidad_recibida' => (int) $m['cantidad_recibida'],
                        'valor_unitario' => is_numeric($m['valor_unitario'] ?? '') ? (float) $m['valor_unitario'] : null,
                        'clasificacion_puntos_id' => $clasificacionId,
                    ]);

                    // Propagamos el ID del catálogo a todos los equipos de este renglón del lote
                    Equipo::where('lote_modelo_id', $registro->id)
                        ->update([
                            'catalogo_equipo_id' => $catalogoId,
                            'marca' => $catItem->marca,
                            'modelo' => $catItem->modelo,
                        ]);

                    if ($clasificacionCambio) {
                        $registro->propagarClasificacion();
                    }
                } else {
                    $registro = LoteModeloRecibido::create([
                        'lote_id' => $lote->id,
                        'catalogo_equipo_id' => $catalogoId,
                        'marca' => $catItem->marca,
                        'modelo' => $catItem->modelo,
                        'cantidad_recibida' => (int) $m['cantidad_recibida'],
                        'valor_unitario' => is_numeric($m['valor_unitario'] ?? '') ? (float) $m['valor_unitario'] : null,
                        'clasificacion_puntos_id' => $clasificacionId,
                    ]);
                }

                // Pre-registrar nuevas series
                $seriesTrimmed = array_values(array_filter(
                    array_map('trim', $m['numeros_serie'] ?? []), fn ($s) => $s !== ''
                ));

                $almacenPreparacion = Almacen::find(Almacen::PREPARACION);

                foreach ($seriesTrimmed as $serie) {
                    if (Equipo::where('numero_serie', $serie)->exists()) {
                        continue;
                    }

                    $equipo = Equipo::create([
                        'numero_serie' => $serie,
                        'lote_modelo_id' => $registro->id,
                        'catalogo_equipo_id' => $catalogoId,
                        'clasificacion_puntos_id' => $registro->clasificacion_puntos_id,
                        'marca' => $m['marca'],
                        'modelo' => $m['modelo'],
                        'estatus_ciclo' => 'PREPARACION',
                        'estatus_area' => 'SIN_ASIGNAR',
                        'registrado_por_user_id' => Auth::id(),
                        'proveedor_id' => $lote->proveedor_id,
                        'almacen_id' => Almacen::PREPARACION,
                        'sucursal_id' => Auth::user()->sucursal_id ?? 1,
                    ]);

                    if ($almacenPreparacion) {
                        app(EquipoMovimientoService::class)->abrirEstanciaInicial(
                            $equipo,
                            $almacenPreparacion,
                            'ALTA_LOTE',
                            'Alta inicial desde edicion de lote'
                        );
                    }
                }
            }
        });

        session()->flash('success', 'Lote actualizado correctamente.');

        return redirect()->route('lotes.editar');
    }

    public function render()
    {
        $marcas = CatalogoEquipo::select('marca')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca')
            ->toArray();

        return view('livewire.preparacion.lotes.editar-lote', [
            'marcas' => $marcas,
            'clasificaciones' => ClasificacionPuntos::where('activo', true)->orderBy('clave')->get(),
        ]);
    }
}
