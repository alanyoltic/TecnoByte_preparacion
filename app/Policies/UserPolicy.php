<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->tienePermiso('sistema.usuarios.ver');
    }

    /**
     * Determine if the user can view the specific target user.
     */
    public function view(User $user, User $target): bool
    {
        if (! $user->tienePermiso('sistema.usuarios.ver')) {
            return false;
        }

        $roleSlug = strtolower(optional($user->role)->slug ?? '');
        if (in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true)) {
            return true;
        }

        return (int) $user->departamento_id === (int) $target->departamento_id;
    }

    /**
     * Determine if the user can create users in their department.
     */
    public function create(User $user): bool
    {
        return $user->tienePermiso('sistema.usuarios.crear');
    }

    /**
     * Determine if the user can update the specific target user.
     */
    public function update(User $user, User $target): bool
    {
        if (! $user->tienePermiso('sistema.usuarios.editar')) {
            return false;
        }

        $roleSlug = strtolower(optional($user->role)->slug ?? '');
        if (in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true)) {
            return true;
        }

        return (int) $user->departamento_id === (int) $target->departamento_id;
    }

    /**
     * Determine if the user can delete/baja the target user.
     */
    public function delete(User $user, User $target): bool
    {
        return $this->update($user, $target);
    }
}
