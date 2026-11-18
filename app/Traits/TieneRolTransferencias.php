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
    // SUCURSALES Y ALMACENES PERMITIDOS
    // =========================================================

    /**
     * Determina si el usuario puede cambiar entre diferentes sucursales.
     * CEO/Admin, Gerente y Líder de Área pueden cambiar sucursales de su depto.
     * Vendedores/Operativos están fijos a su sucursal activa.
     */
    public function puedeCambiarSucursales(): bool
    {
        return $this->esCeoOAdmin() || $this->esGerente() || $this->esLider();
    }

    /**
     * Sucursales a las que tiene acceso el usuario según su rol.
     * CEO/Admin: todas las activas.
     * Gerente/Líder: sucursales donde su departamento tiene almacenes activos o donde es encargado.
     * Operativo/Vendedor: únicamente su sucursal activa asignada.
     */
    public function sucursalesPermitidas(): Collection
    {
        if ($this->esCeoOAdmin()) {
            return \App\Models\Sucursal::where('activo', true)->orderBy('nombre')->get();
        }

        if ($this->esGerente() || $this->esLider()) {
            $deptId = $this->departamento_id;
            $encargadoSucursalIds = $this->almacenesComoEncargado()->pluck('sucursal_id')->filter()->unique()->toArray();

            return \App\Models\Sucursal::where('activo', true)
                ->where(function ($q) use ($deptId, $encargadoSucursalIds) {
                    if ($deptId) {
                        $q->whereHas('almacenes', function ($sub) use ($deptId) {
                            $sub->where('activo', 1)->where('departamento_id', $deptId);
                        });
                    }
                    if (!empty($encargadoSucursalIds)) {
                        $q->orWhereIn('id', $encargadoSucursalIds);
                    }
                })
                ->orderBy('nombre')
                ->get();
        }

        if ($this->sucursal_id) {
            return \App\Models\Sucursal::where('activo', true)
                ->where('id', $this->sucursal_id)
                ->get();
        }

        return collect();
    }

    /**
     * Almacenes que el usuario puede usar como ORIGEN.
     * CEO/Admin: todos los activos (filtrable por sucursal).
     * Gerente/Líder: todos los almacenes de su departamento y donde es encargado (filtrable por sucursal).
     * Vendedor/Operativo: únicamente los almacenes de su propia sucursal activa y departamento.
     */
    public function almacenesPermitidosOrigen(?int $sucursalId = null): Collection
    {
        if ($this->esTecnico()) {
            return collect();
        }

        $query = Almacen::where('activo', 1);

        if ($this->esCeoOAdmin()) {
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }
            return $query->with('sucursal')->orderBy('nombre')->get();
        }

        if ($this->esGerente() || $this->esLider()) {
            $deptId = $this->departamento_id;
            $almacenesEncargadoIds = $this->almacenesComoEncargado()->pluck('id')->toArray();

            $query->where(function ($q) use ($deptId, $almacenesEncargadoIds) {
                if ($deptId) {
                    $q->where('departamento_id', $deptId);
                }
                if (!empty($almacenesEncargadoIds)) {
                    $q->orWhereIn('id', $almacenesEncargadoIds);
                }
            });

            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            return $query->with('sucursal')->orderBy('nombre')->get();
        }

        $sucursalActivaId = $this->sucursal_id;
        $deptId = $this->departamento_id;
        $almacenesEncargadoIds = $this->almacenesComoEncargado()->pluck('id')->toArray();

        $query->where(function ($q) use ($sucursalActivaId, $deptId, $almacenesEncargadoIds) {
            if ($sucursalActivaId) {
                $q->where('sucursal_id', $sucursalActivaId);
                if ($deptId) {
                    $q->where('departamento_id', $deptId);
                }
            }
            if (!empty($almacenesEncargadoIds)) {
                $q->orWhereIn('id', $almacenesEncargadoIds);
            }
        });

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return $query->with('sucursal')->orderBy('nombre')->get();
    }

    /**
     * Almacenes que el usuario puede usar como DESTINO.
     * CEO/Admin: todos los activos (filtrable por sucursal).
     * Gerente/Líder: almacenes de su departamento y donde es encargado (filtrable por sucursal).
     * Vendedor/Operativo: almacenes de su sucursal activa.
     */
    public function almacenesPermitidosDestino(?int $sucursalId = null): Collection
    {
        if ($this->esTecnico()) {
            return collect();
        }

        $query = Almacen::where('activo', 1);

        if ($this->esCeoOAdmin()) {
            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }
            return $query->with('sucursal')->orderBy('nombre')->get();
        }

        if ($this->esGerente() || $this->esLider()) {
            $deptId = $this->departamento_id;
            $almacenesEncargadoIds = $this->almacenesComoEncargado()->pluck('id')->toArray();

            $query->where(function ($q) use ($deptId, $almacenesEncargadoIds) {
                if ($deptId) {
                    $q->where('departamento_id', $deptId);
                }
                if (!empty($almacenesEncargadoIds)) {
                    $q->orWhereIn('id', $almacenesEncargadoIds);
                }
            });

            if ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            }

            return $query->with('sucursal')->orderBy('nombre')->get();
        }

        $sucursalActivaId = $this->sucursal_id;
        $deptId = $this->departamento_id;
        $almacenesEncargadoIds = $this->almacenesComoEncargado()->pluck('id')->toArray();

        $query->where(function ($q) use ($sucursalActivaId, $deptId, $almacenesEncargadoIds) {
            if ($sucursalActivaId) {
                $q->where('sucursal_id', $sucursalActivaId);
                if ($deptId) {
                    $q->where('departamento_id', $deptId);
                }
            }
            if (!empty($almacenesEncargadoIds)) {
                $q->orWhereIn('id', $almacenesEncargadoIds);
            }
        });

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return $query->with('sucursal')->orderBy('nombre')->get();
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

        // Si tiene sucursal asignada (ej. asesores/encargados de sucursales)
        if ($this->sucursal_id) {
            $almacenesSucursal = Almacen::where('activo', 1)
                ->where('sucursal_id', $this->sucursal_id)
                ->pluck('id');

            return $query->where(function ($q) use ($almacenesSucursal) {
                $q->whereIn('almacen_origen_id', $almacenesSucursal)
                    ->orWhereIn('almacen_destino_id', $almacenesSucursal)
                    ->orWhere('created_by', $this->id);
            });
        }

        // Lider / Encargado de almacén
        $almacenesEncargado = $this->almacenesComoEncargado()->pluck('id');

        return $query->where(function ($q) use ($almacenesEncargado) {
            $q->whereIn('almacen_origen_id', $almacenesEncargado)
                ->orWhereIn('almacen_destino_id', $almacenesEncargado);
        });
    }

    // =========================================================
    // HELPERS DE CAPACIDAD Y RUTAS DINÁMICAS
    // =========================================================

    /**
     * Determina si el usuario tiene capacidad operativa para crear transferencias.
     */
    public function puedeCrearTransferencias(): bool
    {
        if ($this->esTecnico()) {
            return false;
        }

        if ($this->esCeoOAdmin() || $this->esGerente()) {
            return true;
        }

        if ($this->tienePermiso('prep.transferencias.crear') || $this->tienePermiso('ventas.transferencias.crear')) {
            return true;
        }

        return $this->almacenesPermitidosOrigen()->isNotEmpty();
    }

    /**
     * Retorna la ruta del listado de transferencias que corresponde al contexto del usuario.
     */
    public function rutaListadoTransferencias(): string
    {
        return route('transferencias.index');
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
