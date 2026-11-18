<?php

namespace App\Livewire\Ventas\Inventario;

use App\Models\Almacen;
use App\Models\AlmacenEncargado;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Almacenes'])]
class AdministracionAlmacenes extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isGlobal = in_array(strtolower(optional($user->role)->slug), ['ceo', 'admin', 'admin_sistema', 'sistemas']);

        $query = Almacen::whereNotIn('id', [
            Almacen::PREPARACION,
            Almacen::GARANTIAS_INTERNAS,
            Almacen::GARANTIAS_EXTERNAS,
            Almacen::CALIDAD,
            Almacen::SCRAP,
            Almacen::PIEZAS_PENDIENTES,
            Almacen::AREA_TRANSFERENCIA,
        ]);

        if (!$isGlobal && $user->departamento_id) {
            $query->where('departamento_id', $user->departamento_id);
        }

        $almacenes = $query->withCount('equipos')
        ->with([
            'encargados' => fn ($q) => $q->where('activo', 1)->with('user'),
        ])
        ->orderBy('nombre')
        ->get();

        // Agrupar los almacenes por departamento si es global
        if ($isGlobal) {
            $departamentos = \App\Models\Departamento::pluck('nombre', 'id')->toArray();
            
            $agrupados = [];
            foreach ($almacenes as $almacen) {
                $deptoNombre = $almacen->departamento_id && isset($departamentos[$almacen->departamento_id]) 
                    ? 'Área de ' . ucfirst(strtolower($departamentos[$almacen->departamento_id])) 
                    : 'Almacenes Globales / Sin Área';
                
                if (!isset($agrupados[$deptoNombre])) {
                    $agrupados[$deptoNombre] = [];
                }
                $agrupados[$deptoNombre][] = $almacen;
            }
            ksort($agrupados);
        } else {
            // Si no es global, solo mandamos un grupo default
            $agrupados = ['Almacenes' => $almacenes];
        }

        $usuarios = User::where('is_active', true)->orderBy('nombre')->get();

        return view('livewire.ventas.inventario.administracion-almacenes', [
            'almacenesAgrupados' => $agrupados,
            'usuarios'  => $usuarios,
        ]);
    }

    public function agregarAdministrador($almacenId, $userId)
    {
        if (empty($userId)) {
            return;
        }

        // Verificar si ya está asignado
        $existente = AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->first();

        if ($existente) {
            $this->dispatch('tb-notify', [
                'type' => 'info',
                'title' => 'Aviso',
                'message' => 'Este usuario ya es administrador de este almacén.'
            ]);
            return;
        }

        // Assign new admin
        AlmacenEncargado::create([
            'almacen_id' => $almacenId,
            'user_id' => $userId,
            'desde' => now(),
            'es_principal' => 1,
            'activo' => 1,
            'creado_por' => auth()->id(),
            'motivo' => 'Asignado desde panel de Almacenes',
        ]);

        $this->dispatch('tb-notify', [
            'type' => 'success',
            'title' => 'Administrador agregado',
            'message' => 'El administrador se ha agregado correctamente.'
        ]);
    }

    public function removerAdministrador($almacenId, $userId)
    {
        AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->update(['activo' => 0, 'hasta' => now()]);

        $this->dispatch('tb-notify', [
            'type' => 'success',
            'title' => 'Administrador removido',
            'message' => 'El administrador ha sido removido exitosamente.'
        ]);
    }
}
