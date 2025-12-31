<?php

namespace App\Livewire\Avisos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Aviso;
use Illuminate\Support\Carbon;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'activos'; // activos | inactivos | todos

    public $modalOpen = false;
    public $editingId = null;

    public $titulo = '';
    public $texto = '';
    public $tag = 'INFO';
    public $color = 'slate';
    public $icono = '';
    public $is_active = true;
    public $pinned = false;
    public $starts_at = null;
    public $ends_at = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'activos'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function openEdit(int $id): void
    {
        $aviso = Aviso::findOrFail($id);

        $this->editingId = $aviso->id;
        $this->titulo    = $aviso->titulo;
        $this->texto     = $aviso->texto;
        $this->tag       = $aviso->tag;
        $this->color     = $aviso->color;
        $this->icono     = $aviso->icono ?? '';
        $this->is_active = (bool) $aviso->is_active;
        $this->pinned    = (bool) $aviso->pinned;

        $this->starts_at = $aviso->starts_at ? $aviso->starts_at->format('Y-m-d\TH:i') : null;
        $this->ends_at   = $aviso->ends_at ? $aviso->ends_at->format('Y-m-d\TH:i') : null;

        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function resetForm(): void
    {
        $this->editingId = null;

        $this->titulo = '';
        $this->texto  = '';
        $this->tag    = 'INFO';
        $this->color  = 'slate';
        $this->icono  = '';

        $this->is_active = true;
        $this->pinned    = false;

        $this->starts_at = null;
        $this->ends_at   = null;
    }

    public function save(): void
    {
        $this->validate([
            'titulo' => 'required|string|max:120',
            'texto'  => 'required|string|max:2000',
            'tag'    => 'required|string|max:30',
            'color'  => 'required|string|max:20',
            'icono'  => 'nullable|string|max:16',
            'is_active' => 'boolean',
            'pinned'    => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $payload = [
            'titulo'    => $this->titulo,
            'texto'     => $this->texto,
            'tag'       => $this->tag,
            'color'     => $this->color,
            'icono'     => $this->icono ?: null,
            'is_active' => (bool) $this->is_active,
            'pinned'    => (bool) $this->pinned,
            'starts_at' => $this->starts_at ? Carbon::parse($this->starts_at) : null,
            'ends_at'   => $this->ends_at ? Carbon::parse($this->ends_at) : null,
        ];

        if ($this->editingId) {
            Aviso::whereKey($this->editingId)->update($payload);
        } else {
            $payload['created_by'] = auth()->id();
            Aviso::create($payload);
        }

        $this->modalOpen = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $this->editingId ? 'Aviso actualizado.' : 'Aviso creado.',
        ]);

        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->is_active = !$aviso->is_active;
        $aviso->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $aviso->is_active ? 'Aviso publicado.' : 'Aviso desactivado.',
        ]);
    }

    public function togglePinned(int $id): void
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->pinned = !$aviso->pinned;
        $aviso->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $aviso->pinned ? 'Aviso fijado.' : 'Aviso desfijado.',
        ]);
    }

    public function delete(int $id): void
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->delete();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Aviso eliminado.',
        ]);
    }

    public function render()
    {
        $q = Aviso::query();

        if ($this->filter === 'activos') {
            $q->where('is_active', true);
        } elseif ($this->filter === 'inactivos') {
            $q->where('is_active', false);
        }

        if ($this->search !== '') {
            $s = '%' . $this->search . '%';
            $q->where(function ($sub) use ($s) {
                $sub->where('titulo', 'like', $s)
                    ->orWhere('texto', 'like', $s)
                    ->orWhere('tag', 'like', $s);
            });
        }

        $avisos = $q->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.avisos.index', [
            'avisos' => $avisos,
        ])->layout('layouts.app');
    }
}
