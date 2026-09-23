<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inserta los permisos del módulo Clientes (Ventas).
 * NO toca ninguna migración anterior ni ningún enum existente.
 */
return new class extends Migration
{
    private array $permisos = [
        ['slug' => 'ventas.clientes.ver',    'descripcion' => 'Ver listado de clientes'],
        ['slug' => 'ventas.clientes.crear',  'descripcion' => 'Crear nuevos clientes'],
        ['slug' => 'ventas.clientes.editar', 'descripcion' => 'Editar datos de clientes'],
        ['slug' => 'ventas.clientes.eliminar','descripcion'=> 'Eliminar clientes'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->permisos as $p) {
            if (!DB::table('permisos')->where('slug', $p['slug'])->exists()) {
                DB::table('permisos')->insert([
                    'slug'        => $p['slug'],
                    'descripcion' => $p['descripcion'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        // Asignar a ceo y gerente
        foreach (['ceo', 'gerente', 'gerente_ventas'] as $rolSlug) {
            $rolId = DB::table('roles')->where('slug', $rolSlug)->value('id');
            if (!$rolId) continue;

            foreach ($this->permisos as $p) {
                $permisoId = DB::table('permisos')->where('slug', $p['slug'])->value('id');
                if (!$permisoId) continue;

                $existe = DB::table('rol_permiso')
                    ->where('rol_id', $rolId)
                    ->where('permiso_id', $permisoId)
                    ->exists();

                if (!$existe) {
                    DB::table('rol_permiso')->insert([
                        'rol_id'     => $rolId,
                        'permiso_id' => $permisoId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->permisos, 'slug');
        $ids = DB::table('permisos')->whereIn('slug', $slugs)->pluck('id');
        DB::table('rol_permiso')->whereIn('permiso_id', $ids)->delete();
        DB::table('permisos')->whereIn('id', $ids)->delete();
    }
};
