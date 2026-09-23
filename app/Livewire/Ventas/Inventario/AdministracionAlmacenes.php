<?php

namespace App\Livewire\Ventas\Inventario;

use App\Models\Almacen;
use App\Models\AlmacenEncargado;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Almacenes — Ventas'])]
class AdministracionAlmacenes extends Component
{
    public function render()
    {
        $almacenes = Almacen::whereNotIn('id', [
            Almacen::PREPARACION,
            Almacen::GARANTIAS_INTERNAS,
            Almacen::GARANTIAS_EXTERNAS,
            Almacen::CALIDAD,
            Almacen::SCRAP,
            Almacen::PIEZAS_PENDIENTES,
            Almacen::AREA_TRANSFERENCIA,
        ])
        ->withCount('equipos')
        ->with([
            'encargados' => fn ($q) => $q->where('activo', 1)->with('user'),
        ])
        ->orderBy('nombre')
        ->get();

        $usuarios = User::where('is_active', true)->orderBy('nombre')->get();

        return view('livewire.ventas.inventario.administracion-almacenes', [
            'almacenes' => $almacenes,
            'usuarios'  => $usuarios,
        ]);
    }

    public function asignarAdministrador($almacenId, $userId)
    {
        if (empty($userId)) {
            // Remove current admin
            AlmacenEncargado::where('almacen_id', $almacenId)
                ->where('activo', 1)
                ->update(['activo' => 0, 'hasta' => now()]);
            
            $this->dispatch('tb-notify', [
                'type' => 'success',
                'title' => 'Administrador removido',
                'message' => 'Se ha removido el administrador del almacén exitosamente.'
            ]);
            return;
        }

        // Disable previous admins
        AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('activo', 1)
            ->update(['activo' => 0, 'hasta' => now()]);

        // Assign new admin
        AlmacenEncargado::create([
            'almacen_id' => $almacenId,
            'user_id' => $userId,
            'desde' => now(),
            'es_principal' => 1,
            'activo' => 1,
            'creado_por' => auth()->id(),
            'motivo' => 'Asignado desde panel de Ventas',
        ]);

        $this->dispatch('tb-notify', [
            'type' => 'success',
            'title' => 'Administrador asignado',
            'message' => 'El administrador se ha asignado correctamente.'
        ]);
    }
}
