<?php

namespace App\Livewire\Dashboard;

use App\Models\LiderModoTecnico;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ConfigurarLideresTecnicos extends Component
{
    public array $lideres = [];

    public function mount(): void
    {
        $this->cargarLideres();
    }

    private function cargarLideres(): void
    {
        // Obtener todos los líderes del sistema (solo rol 'lider')
        $this->lideres = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.is_active', true)
            ->whereIn(DB::raw('LOWER(roles.slug)'), ['lider'])
            ->orderBy('users.nombre')
            ->get([
                'users.id',
                'users.nombre',
                'users.apellido_paterno',
            ])
            ->map(function ($u) {
                $essTecnico = LiderModoTecnico::trabajaComoTecnico($u->id);

                return [
                    'id' => $u->id,
                    'nombre' => trim($u->nombre.' '.$u->apellido_paterno),
                    'esTecnico' => $essTecnico,
                ];
            })
            ->toArray();
    }

    public function toggleLider(int $liderId): void
    {
        $registro = LiderModoTecnico::where('lider_id', $liderId)->first();

        if ($registro) {
            // Toggle: si está activo, desactivar; si está inactivo, activar
            $registro->update(['es_tecnico' => ! $registro->es_tecnico]);
            $estado = $registro->es_tecnico ? 'activado' : 'desactivado';
        } else {
            // No existe: crear como técnico
            LiderModoTecnico::create([
                'lider_id' => $liderId,
                'es_tecnico' => true,
                'configurado_por_id' => auth()->id(),
            ]);
            $estado = 'activado';
        }

        $this->cargarLideres();
        $this->dispatch('toast', type: 'success', message: "Líder $estado como técnico.");
        $this->dispatch('lideresActualizados');
    }

    public function render()
    {
        return view('livewire.dashboard.configurar-lideres-tecnicos');
    }
}
