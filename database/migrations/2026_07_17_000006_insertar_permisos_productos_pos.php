<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permisos = [
        // Productos (catálogo comercial genérico)
        ['slug' => 'ventas.productos.ver',      'descripcion' => 'Ver catálogo de productos'],
        ['slug' => 'ventas.productos.crear',    'descripcion' => 'Crear productos'],
        ['slug' => 'ventas.productos.editar',   'descripcion' => 'Editar productos'],
        ['slug' => 'ventas.productos.eliminar', 'descripcion' => 'Eliminar productos'],

        // Punto de Venta (POS)
        ['slug' => 'ventas.pos.ver',            'descripcion' => 'Acceder al Punto de Venta'],
        ['slug' => 'ventas.pos.cobrar',         'descripcion' => 'Cobrar / completar ventas en el POS'],

        // Historial de ventas
        ['slug' => 'ventas.ventas.ver',         'descripcion' => 'Ver historial de ventas'],
        ['slug' => 'ventas.ventas.cancelar',    'descripcion' => 'Cancelar ventas completadas'],
    ];

    public function up(): void
    {
        $now = now();

        // 1. Insertar permisos (si no existen)
        foreach ($this->permisos as $permiso) {
            if (! DB::table('permisos')->where('slug', $permiso['slug'])->exists()) {
                DB::table('permisos')->insert([
                    'slug'        => $permiso['slug'],
                    'descripcion' => $permiso['descripcion'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        // 2. Asignar todos los nuevos permisos a CEO, gerente y gerente_ventas
        $rolesSlugs = ['ceo', 'gerente', 'gerente_ventas'];

        foreach ($rolesSlugs as $rolSlug) {
            $rolId = DB::table('roles')->where('slug', $rolSlug)->value('id');
            if (! $rolId) continue;

            foreach ($this->permisos as $permiso) {
                $permisoId = DB::table('permisos')->where('slug', $permiso['slug'])->value('id');
                if (! $permisoId) continue;

                $yaAsignado = DB::table('rol_permiso')
                    ->where('rol_id', $rolId)
                    ->where('permiso_id', $permisoId)
                    ->exists();

                if (! $yaAsignado) {
                    DB::table('rol_permiso')->insert([
                        'rol_id'     => $rolId,
                        'permiso_id' => $permisoId,
                    ]);
                }
            }
        }

        // 3. Si existe un rol cajero/vendedor, darle acceso restringido al POS
        $rolVendedor = DB::table('roles')
            ->whereIn('slug', ['cajero', 'vendedor'])
            ->value('id');

        if ($rolVendedor) {
            $permisosVendedor = ['ventas.productos.ver', 'ventas.pos.ver', 'ventas.pos.cobrar', 'ventas.ventas.ver'];

            foreach ($permisosVendedor as $slug) {
                $permisoId = DB::table('permisos')->where('slug', $slug)->value('id');
                if (! $permisoId) continue;

                $yaAsignado = DB::table('rol_permiso')
                    ->where('rol_id', $rolVendedor)
                    ->where('permiso_id', $permisoId)
                    ->exists();

                if (! $yaAsignado) {
                    DB::table('rol_permiso')->insert([
                        'rol_id'     => $rolVendedor,
                        'permiso_id' => $permisoId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->permisos, 'slug');
        $ids   = DB::table('permisos')->whereIn('slug', $slugs)->pluck('id');

        DB::table('rol_permiso')->whereIn('permiso_id', $ids)->delete();
        DB::table('usuario_permiso')->whereIn('permiso_id', $ids)->delete();
        DB::table('permisos')->whereIn('slug', $slugs)->delete();
    }
};
