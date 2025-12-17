<?php

namespace App\Livewire\Lotes;

use Livewire\Component;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;

class RegistrarLote extends Component
{
    public $nombre_lote;
    public $proveedor_id;
    public $fecha_llegada;
    public $modelos = [];

    public function mount()
    {
        $this->fecha_llegada = now()->toDateString();

        // Una fila por defecto
        $this->modelos = [
            [
                'marca'             => '',
                'modelo'            => '',
                'cantidad_recibida' => 1,
            ],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'marca'             => '',
            'modelo'            => '',
            'cantidad_recibida' => 1,
        ];
    }

    public function removeModeloRow($index)
    {
        unset($this->modelos[$index]);
        $this->modelos = array_values($this->modelos);

        // Nunca dejar sin filas
        if (count($this->modelos) === 0) {
            $this->addModeloRow();
        }
    }

    public function guardar()
    {
        $this->validate([
            'nombre_lote'   => 'required|string|max:255',
            'proveedor_id'  => 'required|exists:proveedores,id',
            'fecha_llegada' => 'nullable|date',

            'modelos'                     => 'required|array|min:1',
            'modelos.*.marca'             => 'required|string|max:100',
            'modelos.*.modelo'            => 'required|string|max:255',
            'modelos.*.cantidad_recibida' => 'required|integer|min:1',
        ], [
            'modelos.required' => 'Debes agregar al menos un modelo al lote.',
        ]);

        DB::transaction(function () {
            // Crear el lote
            $loteId = DB::table('lotes')->insertGetId([
                'nombre_lote'   => $this->nombre_lote,
                'proveedor_id'  => $this->proveedor_id,
                'fecha_llegada' => $this->fecha_llegada ?: null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Crear los modelos recibidos
            $rows = [];
            foreach ($this->modelos as $m) {
                $rows[] = [
                    'lote_id'           => $loteId,
                    'marca'             => $m['marca'],
                    'modelo'            => $m['modelo'],
                    'cantidad_recibida' => $m['cantidad_recibida'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            DB::table('lote_modelos_recibidos')->insert($rows);
        });

        $this->dispatch('toast', type: 'success', message: 'Lote y modelos registrados correctamente.');


        // Reset
        $this->reset(['nombre_lote', 'proveedor_id', 'modelos']);
        $this->fecha_llegada = now()->toDateString();
        $this->modelos = [
            [
                'marca'             => '',
                'modelo'            => '',
                'cantidad_recibida' => 1,
            ],
        ];
    }

    public function render()
    {
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();

        return view('livewire.lotes.registrar-lote', [
            'proveedores' => $proveedores,
        ]);
    }



    public function probarNotificaciones()
{
    $tipos = ['success','error','warning','info'];
    $type = $tipos[array_rand($tipos)];

    $msg = match ($type) {
        'success' => '✅ Toast de éxito (prueba).',
        'error'   => '❌ Toast de error (prueba).',
        'warning' => '⚠️ Toast de aviso (prueba).',
        default   => 'ℹ️ Toast de info (prueba).',
    };

    $this->dispatch('toast', type: $type, message: $msg);
}

}
