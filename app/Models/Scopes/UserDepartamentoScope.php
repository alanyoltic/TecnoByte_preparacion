<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserDepartamentoScope implements Scope
{
    private static bool $isApplying = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Evitar recursión infinita cuando Auth resuelve el usuario autenticado
        if (self::$isApplying) {
            return;
        }

        self::$isApplying = true;

        try {
            // Verificar si hay una sesión de Auth activa sin gatillar consultas extras
            if (! Auth::hasUser()) {
                return;
            }

            $user = Auth::user();
            if (! $user) {
                return;
            }

            $roleSlug = strtolower(optional($user->role)->slug ?? '');

            // CEO y Administradores de sistema ven a todos los usuarios
            if (in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true)) {
                return;
            }

            // Para cualquier otro perfil, sólo ver usuarios de su mismo departamento_id
            if ($user->departamento_id) {
                $builder->where($model->getTable() . '.departamento_id', $user->departamento_id);
            }
        } finally {
            self::$isApplying = false;
        }
    }
}
