<?php

namespace App\Livewire\Preparacion\Lotes;

use App\Models\Almacen;
use App\Models\Equipo;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\LoteModeloRecibido;
use App\Models\ClasificacionPuntos;

class EditarLote extends Component
{
    public int $loteId;

    public ?Lote $lote = null;

    public $nombre_lote;
    public $proveedor_id;
    public $fecha_llegada;

    public $proveedores = [];

    public array $modelos        = [];
    public array $deleteModeloIds = [];

    public bool $modalSeries   = false;
    public int  $serialesIndex = -1;

    public function mount($loteId)
    {
        abort_unless(auth()->user()?->tienePermiso('prep.lotes.gestion'), 403);

        $this->loteId = (int) $loteId;

        $this->proveedores = Proveedor::orderBy('abreviacion')->get();

        $this->lote = Lote::with(['modelosRecibidos' => function ($q) {
            $q->withCount('equipos')->orderBy('id');
        }])->findOrFail($this->loteId);

        $this->nombre_lote   = $this->lote->nombre_lote;
        $this->proveedor_id  = $this->lote->proveedor_id;
        $this->fecha_llegada = $this->lote->fecha_llegada;

        $this->modelos = $this->lote->modelosRecibidos->map(function ($m) {
            return [
                'id'                      => $m->id,
                'marca'                   => $m->marca,
                'modelo'                  => $m->modelo,
                'cantidad_recibida'       => (int) $m->cantidad_recibida,
                'valor_unitario'          => $m->valor_unitario !== null ? (string) $m->valor_unitario : '',
                'clasificacion_puntos_id' => $m->clasificacion_puntos_id,
                'equipos_registrados'     => (int) ($m->equipos_count ?? 0),
                'numeros_serie'           => [],
            ];
        })->toArray();

        if (count($this->modelos) === 0) {
            $this->modelos = [[
                'id'                      => null,
                'marca'                   => '',
                'modelo'                  => '',
                'cantidad_recibida'       => 1,
                'valor_unitario'          => '',
                'clasificacion_puntos_id' => null,
                'equipos_registrados'     => 0,
                'numeros_serie'           => [],
            ]];
        }
    }

    protected function rules()
    {
        return [
            'nombre_lote'   => ['required', 'string', 'max:255'],
            'proveedor_id'  => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_llegada' => ['nullable', 'date'],

            'modelos'                             => ['required', 'array', 'min:1'],
            'modelos.*.marca'                     => ['required', 'string', 'max:100'],
            'modelos.*.modelo'                    => ['required', 'string', 'max:255'],
            'modelos.*.cantidad_recibida'         => ['required', 'integer', 'min:1'],
            'modelos.*.valor_unitario'            => ['nullable', 'numeric', 'min:0'],
            'modelos.*.clasificacion_puntos_id'   => ['nullable', 'exists:clasificaciones_puntos,id'],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'id'                      => null,
            'marca'                   => '',
            'modelo'                  => '',
            'cantidad_recibida'       => 1,
            'valor_unitario'          => '',
            'clasificacion_puntos_id' => null,
            'equipos_registrados'     => 0,
            'numeros_serie'           => [],
        ];
    }

    public function removeModeloRow($index)
    {
        if (!isset($this->modelos[$index])) return;

        $row = $this->modelos[$index];

        if (!empty($row['id']) && ((int) ($row['equipos_registrados'] ?? 0) > 0)) {
            throw ValidationException::withMessages([
                'modelos' => 'No puedes eliminar un modelo que ya tiene equipos registrados.',
            ]);
        }

        if (!empty($row['id'])) {
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

    /** Series ya en DB para el modelo actualmente abierto (read-only en modal). */
    public function seriesExistentes(): array
    {
        if ($this->serialesIndex < 0) return [];
        $modeloId = $this->modelos[$this->serialesIndex]['id'] ?? null;
        if (!$modeloId) return [];

        return Equipo::where('lote_modelo_id', $modeloId)
            ->where('estatus_area', Equipo::AREA_EN_ESPERA)
            ->pluck('numero_serie')
            ->toArray();
    }

    // ── Validaciones ──────────────────────────────────────────────────────

    private function validarCantidadesVsRegistrados()
    {
        foreach ($this->modelos as $i => $m) {
            $registrados = (int) ($m['equipos_registrados'] ?? 0);
            $cantidad    = (int) ($m['cantidad_recibida'] ?? 0);

            if ($cantidad < $registrados) {
                throw ValidationException::withMessages([
                    "modelos.$i.cantidad_recibida" =>
                        "La cantidad no puede ser menor a los equipos ya registrados ($registrados).",
                ]);
            }
        }
    }

    public function updatedModelos($value, $name): void
    {
        if (!str_ends_with($name, 'cantidad_recibida')) {
            return;
        }

        $index = (int) explode('.', $name)[0];

        $registrados = (int) ($this->modelos[$index]['equipos_registrados'] ?? 0);
        $cantidad    = (int) ($this->modelos[$index]['cantidad_recibida'] ?? 0);

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
                'nombre_lote'   => $this->nombre_lote,
                'proveedor_id'  => $this->proveedor_id,
                'fecha_llegada' => $this->fecha_llegada,
            ]);

            if (!empty($this->deleteModeloIds)) {
                LoteModeloRecibido::where('lote_id', $lote->id)
                    ->whereIn('id', $this->deleteModeloIds)
                    ->delete();
            }

            foreach ($this->modelos as $m) {

                $clasificacionId = $m['clasificacion_puntos_id'] ?: null;

                if (!empty($m['id'])) {
                    $registro = LoteModeloRecibido::where('lote_id', $lote->id)
                        ->where('id', (int) $m['id'])
                        ->firstOrFail();

                    $clasificacionCambio = $registro->clasificacion_puntos_id != $clasificacionId;

                    $registro->update([
                        'marca'                   => $m['marca'],
                        'modelo'                  => $m['modelo'],
                        'cantidad_recibida'       => (int) $m['cantidad_recibida'],
                        'valor_unitario'          => is_numeric($m['valor_unitario'] ?? '') ? (float) $m['valor_unitario'] : null,
                        'clasificacion_puntos_id' => $clasificacionId,
                    ]);

                    if ($clasificacionCambio) {
                        $registro->propagarClasificacion();
                    }
                } else {
                    $registro = LoteModeloRecibido::create([
                        'lote_id'                 => $lote->id,
                        'marca'                   => $m['marca'],
                        'modelo'                  => $m['modelo'],
                        'cantidad_recibida'       => (int) $m['cantidad_recibida'],
                        'valor_unitario'          => is_numeric($m['valor_unitario'] ?? '') ? (float) $m['valor_unitario'] : null,
                        'clasificacion_puntos_id' => $clasificacionId,
                    ]);
                }

                // Pre-registrar nuevas series
                $seriesTrimmed = array_values(array_filter(
                    array_map('trim', $m['numeros_serie'] ?? []), fn ($s) => $s !== ''
                ));

                foreach ($seriesTrimmed as $serie) {
                    if (Equipo::where('numero_serie', $serie)->exists()) continue;

                    Equipo::create([
                        'numero_serie'           => $serie,
                        'lote_modelo_id'         => $registro->id,
                        'marca'                  => $m['marca'],
                        'modelo'                 => $m['modelo'],
                        'estatus_ciclo'          => 'PREPARACION',
                        'estatus_area'           => 'EN_ESPERA',
                        'registrado_por_user_id' => Auth::id(),
                        'proveedor_id'           => $lote->proveedor_id,
                        'almacen_id'             => Almacen::PREPARACION,
                        'sucursal_id'            => Auth::user()->sucursal_id ?? 1,
                    ]);
                }
            }
        });

        session()->flash('success', 'Lote actualizado correctamente.');
        return redirect()->route('lotes.editar');
    }

    public function render()
    {
        return view('livewire.preparacion.lotes.editar-lote', [
            'clasificaciones' => ClasificacionPuntos::where('activo', true)->orderBy('clave')->get(),
        ]);
    }
}
