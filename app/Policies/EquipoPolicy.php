<?php

namespace App\Policies;

use App\Models\Equipo;
use App\Models\User;

class EquipoPolicy
{
    /**
     * Determine if the user can view the equipment.
     */
    public function view(User $user, Equipo $equipo): bool
    {
        $roleSlug = strtolower(optional($user->role)->slug ?? '');

        // CEO y Admins ven cualquier equipo en cualquier ciclo
        if (in_array($roleSlug, ['ceo', 'admin_sistema', 'sistemas'], true)) {
            return true;
        }

        $userDepto = strtoupper(optional($user->departamento)->clave ?? '');

        // Ventas sólo ve equipos en etapas del módulo de Ventas (VENTAS, APARTADO, VENDIDO)
        if ($userDepto === 'VENTAS') {
            return in_array($equipo->estatus_ciclo, [
                Equipo::CICLO_VENTAS,
                Equipo::CICLO_APARTADO,
                Equipo::CICLO_VENDIDO,
            ], true);
        }

        // Preparación sólo ve equipos en etapas de Preparación / Calidad / CEDIS
        if ($userDepto === 'PREPARACION') {
            return in_array($equipo->estatus_ciclo, [
                Equipo::CICLO_CEDIS,
                Equipo::CICLO_PREPARACION,
                Equipo::CICLO_CALIDAD,
            ], true);
        }

        return true;
    }

    /**
     * Determine if the user can update the equipment.
     */
    public function update(User $user, Equipo $equipo): bool
    {
        return $this->view($user, $equipo);
    }
}
