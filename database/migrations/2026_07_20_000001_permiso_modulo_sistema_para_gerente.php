<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Permite que el rol "gerente" acceda a la gestión de su propio personal.
 *
 * El rol "gerente" ya tiene: sistema.usuarios.ver / crear / editar
 * Solo le faltaba "modulo.sistema" para superar el middleware de las rutas.
 *
 * El UserController ya filtra por departamento_id automáticamente,
 * así que el gerente de Ventas solo verá usuarios de Ventas.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permisoId = DB::table('permisos')
            ->where('slug', 'modulo.sistema')
            ->value('id');

        if (! $permisoId) {
            return; // El permiso no existe, nada que hacer
        }

        $rolGerenteId = DB::table('roles')
            ->where('slug', 'gerente')
            ->value('id');

        if (! $rolGerenteId) {
            return;
        }

        $yaAsignado = DB::table('rol_permiso')
            ->where('rol_id', $rolGerenteId)
            ->where('permiso_id', $permisoId)
            ->exists();

        if (! $yaAsignado) {
            DB::table('rol_permiso')->insert([
                'rol_id'     => $rolGerenteId,
                'permiso_id' => $permisoId,
            ]);
        }
    }

    public function down(): void
    {
        $permisoId = DB::table('permisos')
            ->where('slug', 'modulo.sistema')
            ->value('id');

        $rolGerenteId = DB::table('roles')
            ->where('slug', 'gerente')
            ->value('id');

        if ($permisoId && $rolGerenteId) {
            DB::table('rol_permiso')
                ->where('rol_id', $rolGerenteId)
                ->where('permiso_id', $permisoId)
                ->delete();
        }
    }
};
