<?php

namespace App\Traits;

use App\Models\Almacen;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait TieneRolTransferencias
{
    // =========================================================
    // HELPERS DE ROL
    // =========================================================

    public function esCeoOAdmin(): bool
    {
        return in_array($this->role?->slug, ['ceo', 'admin', 'admin_sistema'], true);
    }

    public function esGerente(): bool
    {
        return $this->role?->slug === 'gerente';
    }

    public function esLider(): bool
    {
        return $this->role?->slug === 'lider';
    }

    public function esTecnico(): bool
    {
        return $this->role?->slug === 'tecnico';
    }

    // =========================================================
    // ALMACENES PERMITIDOS
    // =========================================================

    /**
     * Almacenes que el usuario puede usar como ORIGEN.
     * CEO/Admin: todos los activos.
     * Gerente:   todos los activos (puede mover desde cualquier almacén de su depto y compartidos).
     * Lider:     solo donde es encargado activo.
     * Tecnico:   ninguno.
     */
    public function almacenesPermitidosOrigen(): Collection
    {
        if ($this->esTecnico()) {
            return collect();
        }

        if ($this->esCeoOAdmin() || $this->esGerente()) {
            return Almacen::where('activo', 1)->orderBy('nombre')->get();
        }

        // Lider: solo almacenes donde es encargado activo vigente
        return $this->almacenesComoEncargado();
    }

    /**
     * Almacenes que el usuario puede usar como DESTINO.
     * CEO/Admin/Gerente: todos los activos.
     * Lider:             todos los activos (puede enviar a cualquier lado).
     * Tecnico:           ninguno.
     */
    public function almacenesPermitidosDestino(): Collection
    {
        if ($this->esTecnico()) {
            return collect();
        }

        return Almacen::where('activo', 1)->orderBy('nombre')->get();
    }

    // =========================================================
    // FILTRO DE VISIBILIDAD EN LISTADO
    // =========================================================

    /**
     * Aplica un WHERE a un query de Transferencia según el rol del usuario.
     *
     * CEO/Admin : ve todas.
     * Gerente   : ve las de su departamento (origen o destino) + las que él creó.
     * Lider     : ve las de los almacenes donde es encargado (origen o destino).
     * Tecnico   : no ve ninguna.
     */
    public function aplicarFiltroVisibilidadTransferencias($query)
    {
        if ($this->esTecnico()) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->esCeoOAdmin()) {
            return $query; // sin filtro
        }

        if ($this->esGerente()) {
            $almacenesDepto = Almacen::where('activo', 1)
                ->where('departamento_id', $this->departamento_id)
                ->pluck('id');

            return $query->where(function ($q) use ($almacenesDepto) {
                $q->whereIn('almacen_origen_id', $almacenesDepto)
                    ->orWhereIn('almacen_destino_id', $almacenesDepto)
                    ->orWhere('created_by', $this->id);
            });
        }

        // Lider
        $almacenesEncargado = $this->almacenesComoEncargado()->pluck('id');

        return $query->where(function ($q) use ($almacenesEncargado) {
            $q->whereIn('almacen_origen_id', $almacenesEncargado)
                ->orWhereIn('almacen_destino_id', $almacenesEncargado);
        });
    }

    // =========================================================
    // HELPER PRIVADO
    // =========================================================

    /**
     * Almacenes donde el usuario es encargado activo y vigente (por fecha).
     */
    private function almacenesComoEncargado(): Collection
    {
        return Almacen::where('activo', 1)
            ->whereHas('encargados', function ($q) {
                $q->where('user_id', $this->id)
                    ->where('activo', 1)
                    ->where('desde', '<=', Carbon::now())
                    ->where(function ($q2) {
                        $q2->whereNull('hasta')
                            ->orWhere('hasta', '>=', Carbon::now());
                    });
            })
            ->orderBy('nombre')
            ->get();
    }
}
