<?php

namespace App\Livewire\Ventas;

use App\Models\Cliente;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['pageTitle' => 'Clientes — Ventas'])]
class Clientes extends Component
{
    use WithPagination;

    // ── Filtros listado ────────────────────────────────────────────
    public string $busqueda = '';
    public string $filtroTipo = '';

    // ── Modal crear / editar ───────────────────────────────────────
    public bool $modalAbierto = false;
    public ?int $clienteId = null;

    // ── Campos del formulario ──────────────────────────────────────
    public string $tipo_persona       = 'FISICA';
    public string $nombres            = '';
    public string $apellidos          = '';
    public string $razon_social       = '';
    public string $rfc                = '';
    public string $regimen_fiscal     = '';
    public string $uso_cfdi           = '';
    public ?int   $vendedor_id        = null;
    public string $como_se_entero     = '';
    public string $correo             = '';
    public string $telefono           = '';
    public string $pais               = 'México';
    public string $estado             = '';
    public string $municipio          = '';
    public string $localidad          = '';
    public string $colonia            = '';
    public string $calle              = '';
    public string $no_ext             = '';
    public string $no_int             = '';
    public string $codigo_postal      = '';
    public string $codigo_colonia     = '';
    public string $codigo_localidad   = '';
    public string $notas              = '';

    // ── Modal eliminar ─────────────────────────────────────────────
    public bool $modalEliminar = false;
    public ?int $eliminarId = null;

    // ── Listeners ──────────────────────────────────────────────────
    protected $listeners = ['closeModal' => 'cerrarModal'];

    // ==========================================================
    // RENDER
    // ==========================================================

    public function render()
    {
        $clientes = Cliente::query()
            ->with('vendedor')
            ->when($this->busqueda, function ($q) {
                $b = '%' . $this->busqueda . '%';
                $q->where(function ($q2) use ($b) {
                    $q2->where('nombres', 'like', $b)
                        ->orWhere('apellidos', 'like', $b)
                        ->orWhere('razon_social', 'like', $b)
                        ->orWhere('rfc', 'like', $b)
                        ->orWhere('correo', 'like', $b)
                        ->orWhere('telefono', 'like', $b);
                });
            })
            ->when($this->filtroTipo, fn ($q) => $q->where('tipo_persona', $this->filtroTipo))
            ->orderByDesc('created_at')
            ->paginate(15);

        $vendedores = User::select('id', 'nombre', 'apellido_paterno')
            ->orderBy('nombre')
            ->get();

        return view('livewire.ventas.clientes', [
            'clientes'              => $clientes,
            'vendedores'            => $vendedores,
            'opcionesComoSeEntero'  => Cliente::opcionesComoSeEntero(),
            'opcionesRegimen'       => Cliente::opcionesRegimenFiscal(),
            'opcionesUsoCfdi'       => Cliente::opcionesUsoCfdi(),
        ]);
    }

    // ==========================================================
    // MODAL
    // ==========================================================

    public function abrirCrear()
    {
        $this->resetFormulario();
        $this->clienteId = null;
        $this->modalAbierto = true;
    }

    public function abrirEditar(int $id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->clienteId      = $cliente->id;
        $this->tipo_persona   = $cliente->tipo_persona;
        $this->nombres        = $cliente->nombres ?? '';
        $this->apellidos      = $cliente->apellidos ?? '';
        $this->razon_social   = $cliente->razon_social ?? '';
        $this->rfc            = $cliente->rfc ?? '';
        $this->regimen_fiscal = $cliente->regimen_fiscal ?? '';
        $this->uso_cfdi       = $cliente->uso_cfdi ?? '';
        $this->vendedor_id    = $cliente->vendedor_id;
        $this->como_se_entero = $cliente->como_se_entero ?? '';
        $this->correo         = $cliente->correo ?? '';
        $this->telefono       = $cliente->telefono ?? '';
        $this->pais           = $cliente->pais ?? 'México';
        $this->estado         = $cliente->estado ?? '';
        $this->municipio      = $cliente->municipio ?? '';
        $this->localidad      = $cliente->localidad ?? '';
        $this->colonia        = $cliente->colonia ?? '';
        $this->calle          = $cliente->calle ?? '';
        $this->no_ext         = $cliente->no_ext ?? '';
        $this->no_int         = $cliente->no_int ?? '';
        $this->codigo_postal  = $cliente->codigo_postal ?? '';
        $this->codigo_colonia  = $cliente->codigo_colonia ?? '';
        $this->codigo_localidad = $cliente->codigo_localidad ?? '';
        $this->notas          = $cliente->notas ?? '';
        $this->modalAbierto   = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetFormulario();
    }

    // ==========================================================
    // GUARDAR
    // ==========================================================

    public function guardar()
    {
        $this->validate($this->reglasValidacion());

        $data = [
            'tipo_persona'     => $this->tipo_persona,
            'nombres'          => $this->tipo_persona === 'FISICA' ? trim($this->nombres) ?: null : null,
            'apellidos'        => $this->tipo_persona === 'FISICA' ? trim($this->apellidos) ?: null : null,
            'razon_social'     => $this->tipo_persona === 'MORAL' ? trim($this->razon_social) ?: null : null,
            'rfc'              => trim($this->rfc) ?: null,
            'regimen_fiscal'   => trim($this->regimen_fiscal) ?: null,
            'uso_cfdi'         => trim($this->uso_cfdi) ?: null,
            'vendedor_id'      => $this->vendedor_id ?: null,
            'como_se_entero'   => trim($this->como_se_entero) ?: null,
            'correo'           => trim($this->correo) ?: null,
            'telefono'         => trim($this->telefono) ?: null,
            'pais'             => trim($this->pais) ?: 'México',
            'estado'           => trim($this->estado) ?: null,
            'municipio'        => trim($this->municipio) ?: null,
            'localidad'        => trim($this->localidad) ?: null,
            'colonia'          => trim($this->colonia) ?: null,
            'calle'            => trim($this->calle) ?: null,
            'no_ext'           => trim($this->no_ext) ?: null,
            'no_int'           => trim($this->no_int) ?: null,
            'codigo_postal'    => trim($this->codigo_postal) ?: null,
            'codigo_colonia'   => trim($this->codigo_colonia) ?: null,
            'codigo_localidad' => trim($this->codigo_localidad) ?: null,
            'notas'            => trim($this->notas) ?: null,
        ];

        if ($this->clienteId) {
            Cliente::findOrFail($this->clienteId)->update($data);
            $this->dispatch('notify', tipo: 'success', mensaje: 'Cliente actualizado correctamente.');
        } else {
            Cliente::create($data);
            $this->dispatch('notify', tipo: 'success', mensaje: 'Cliente registrado correctamente.');
        }

        $this->cerrarModal();
        $this->resetPage();
    }

    // ==========================================================
    // ELIMINAR
    // ==========================================================

    public function confirmarEliminar(int $id)
    {
        $this->eliminarId = $id;
        $this->modalEliminar = true;
    }

    public function eliminar()
    {
        if ($this->eliminarId) {
            Cliente::findOrFail($this->eliminarId)->delete();
            $this->dispatch('notify', tipo: 'success', mensaje: 'Cliente eliminado.');
        }
        $this->modalEliminar = false;
        $this->eliminarId = null;
    }

    // ==========================================================
    // HELPERS
    // ==========================================================

    private function resetFormulario()
    {
        $this->tipo_persona     = 'FISICA';
        $this->nombres          = '';
        $this->apellidos        = '';
        $this->razon_social     = '';
        $this->rfc              = '';
        $this->regimen_fiscal   = '';
        $this->uso_cfdi         = '';
        $this->vendedor_id      = null;
        $this->como_se_entero   = '';
        $this->correo           = '';
        $this->telefono         = '';
        $this->pais             = 'México';
        $this->estado           = '';
        $this->municipio        = '';
        $this->localidad        = '';
        $this->colonia          = '';
        $this->calle            = '';
        $this->no_ext           = '';
        $this->no_int           = '';
        $this->codigo_postal    = '';
        $this->codigo_colonia   = '';
        $this->codigo_localidad = '';
        $this->notas            = '';
        $this->resetValidation();
    }

    private function reglasValidacion(): array
    {
        $base = [
            'tipo_persona'   => 'required|in:FISICA,MORAL',
            'rfc'            => 'nullable|string|max:13',
            'regimen_fiscal' => 'nullable|string|max:100',
            'correo'         => 'nullable|email|max:180',
            'codigo_postal'  => 'nullable|string|max:5',
        ];

        if ($this->tipo_persona === 'FISICA') {
            $base['nombres']   = 'nullable|string|max:150';
            $base['apellidos'] = 'nullable|string|max:150';
        } else {
            $base['razon_social'] = 'nullable|string|max:200';
        }

        return $base;
    }
}
