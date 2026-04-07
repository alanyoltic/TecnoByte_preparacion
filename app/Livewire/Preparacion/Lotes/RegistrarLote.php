<?php

namespace App\Livewire\Preparacion\Lotes;

use Livewire\Component;
use App\Models\Proveedor;
use App\Models\ClasificacionPuntos;
use App\Models\LoteModeloRecibido;
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
                'marca'                  => '',
                'modelo'                 => '',
                'cantidad_recibida'      => 1,
                'valor_unitario'         => '',
                'clasificacion_puntos_id'=> null,
            ],
        ];
    }

    public function addModeloRow()
    {
        $this->modelos[] = [
            'marca'                  => '',
            'modelo'                 => '',
            'cantidad_recibida'      => 1,
            'clasificacion_puntos_id'=> null,
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

            'modelos'                              => 'required|array|min:1',
            'modelos.*.marca'                      => 'required|string|max:100',
            'modelos.*.modelo'                     => 'required|string|max:255',
            'modelos.*.cantidad_recibida'           => 'required|integer|min:1',
            'modelos.*.valor_unitario'              => 'nullable|numeric|min:0',
            'modelos.*.clasificacion_puntos_id'     => 'nullable|exists:clasificaciones_puntos,id',
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

            // Crear los modelos recibidos y propagar clasificación
            foreach ($this->modelos as $m) {
                $loteModelo = LoteModeloRecibido::create([
                    'lote_id'                => $loteId,
                    'marca'                  => $m['marca'],
                    'modelo'                 => $m['modelo'],
                    'cantidad_recibida'      => $m['cantidad_recibida'],
                    'valor_unitario'         => is_numeric($m['valor_unitario'] ?? '') ? (float)$m['valor_unitario'] : null,
                    'clasificacion_puntos_id'=> $m['clasificacion_puntos_id'] ?: null,
                ]);

                // Los equipos de este lote aún no existen, pero la clasificación
                // queda en lote_modelo_recibido para asignarse al registrar cada equipo.
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Lote y modelos registrados correctamente.');


        // Reset
        $this->reset(['nombre_lote', 'proveedor_id', 'modelos']);
        $this->fecha_llegada = now()->toDateString();
        $this->modelos = [
            [
                'marca'                  => '',
                'modelo'                 => '',
                'cantidad_recibida'      => 1,
                'valor_unitario'         => '',
                'clasificacion_puntos_id'=> null,
            ],
        ];
    }

    public function render()
    {
        $proveedores     = Proveedor::orderBy('nombre_empresa')->get();
        $clasificaciones = ClasificacionPuntos::where('activo', true)->orderBy('clave')->get();

        return view('livewire.preparacion.lotes.registrar-lote', [
            'proveedores'     => $proveedores,
            'clasificaciones' => $clasificaciones,
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
