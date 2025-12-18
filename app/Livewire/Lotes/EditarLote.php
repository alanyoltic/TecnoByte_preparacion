<?php

namespace App\Livewire\Lotes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\LoteModeloRecibido;

class EditarLote extends Component
{
    public int $loteId;

    public ?Lote $lote = null;

    public $nombre_lote;
    public $proveedor_id;
    public $fecha_llegada;

    public $proveedores = [];

    public array $modelos = [];
    public array $deleteModeloIds = [];

    public function mount($loteId)
    {
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
                'id'               => $m->id,
                'marca'            => $m->marca,
                'modelo'           => $m->modelo,
                'cantidad_recibida'=> (int) $m->cantidad_recibida,
                'equipos_registrados' => (int) ($m->equipos_count ?? 0),
            ];
        })->toArray();

        if (count($this->modelos) === 0) {
            $this->modelos = [[
                'id' => null,
                'marca' => '',
                'modelo' => '',
                'cantidad_recibida' => 1,
                'equipos_registrados' => 0,
            ]];
        }
    }

    protected function rules()
    {
        return [
            'nombre_lote'   => ['required', 'string', 'max:255'],
            'proveedor_id'  => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_llegada' => ['nullable', 'date'],

            'modelos' => ['required', 'array', 'min:1'],
            'modelos.*.marca' => ['required', 'string', 'max:100'],
            'modelos.*.modelo' => ['required', 'string', 'max:255'],
            'modelos.*.cantidad_recibida' => ['required', 'integer', 'min:1'],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'id' => null,
            'marca' => '',
            'modelo' => '',
            'cantidad_recibida' => 1,
            'equipos_registrados' => 0,
        ];
    }

    public function removeModeloRow($index)
    {
        if (!isset($this->modelos[$index])) return;

        $row = $this->modelos[$index];

        // Si ya existe en BD y tiene equipos, no se puede borrar
        if (!empty($row['id']) && ((int)($row['equipos_registrados'] ?? 0) > 0)) {
            throw ValidationException::withMessages([
                'modelos' => 'No puedes eliminar un modelo que ya tiene equipos registrados.',
            ]);
        }

        // Si existe en BD, marcar para eliminar
        if (!empty($row['id'])) {
            $this->deleteModeloIds[] = (int) $row['id'];
        }

        unset($this->modelos[$index]);
        $this->modelos = array_values($this->modelos);

        if (count($this->modelos) === 0) {
            $this->addModeloRow();
        }
    }

    private function validarCantidadesVsRegistrados()
    {
        foreach ($this->modelos as $i => $m) {
            $registrados = (int)($m['equipos_registrados'] ?? 0);
            $cantidad    = (int)($m['cantidad_recibida'] ?? 0);

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
    // Ej: "2.cantidad_recibida"
    if (!str_ends_with($name, 'cantidad_recibida')) {
        return;
    }

    $index = (int) explode('.', $name)[0];

    $registrados = (int)($this->modelos[$index]['equipos_registrados'] ?? 0);
    $cantidad    = (int)($this->modelos[$index]['cantidad_recibida'] ?? 0);

    if ($cantidad < $registrados) {
        // Revertir al mínimo permitido
        $this->modelos[$index]['cantidad_recibida'] = $registrados;

        // Error visible en el campo
        $this->addError("modelos.$index.cantidad_recibida",
            "Mínimo permitido: $registrados (ya registrados)."
        );

        // Toast/alerta (usa el nombre que manejes en tu x-toast)
        $this->dispatch('toast', type: 'error', message: "No puedes bajar de $registrados porque ya hay equipos registrados.");
    }
}


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

            // Eliminar modelos marcados
            if (!empty($this->deleteModeloIds)) {
                LoteModeloRecibido::where('lote_id', $lote->id)
                    ->whereIn('id', $this->deleteModeloIds)
                    ->delete();
            }

            // Upsert de modelos actuales
            foreach ($this->modelos as $m) {

                if (!empty($m['id'])) {
                    // update
                    $registro = LoteModeloRecibido::where('lote_id', $lote->id)
                        ->where('id', (int)$m['id'])
                        ->firstOrFail();

                    $registro->update([
                        'marca' => $m['marca'],
                        'modelo' => $m['modelo'],
                        'cantidad_recibida' => (int)$m['cantidad_recibida'],
                    ]);

                } else {
                    // create
                    LoteModeloRecibido::create([
                        'lote_id' => $lote->id,
                        'marca' => $m['marca'],
                        'modelo' => $m['modelo'],
                        'cantidad_recibida' => (int)$m['cantidad_recibida'],
                    ]);
                }
            }
        });

        session()->flash('success', 'Lote actualizado correctamente.');
        return redirect()->route('lotes.editar');
    }

    public function render()
    {
        return view('livewire.lotes.editar-lote');
    }
}
