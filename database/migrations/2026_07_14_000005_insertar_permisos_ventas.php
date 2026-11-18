<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inserta los permisos del módulo Ventas y los de Despacho a Ventas (Preparación).
 * Asigna todos los permisos de ventas al rol 'ceo' (y opcionalmente a otros roles).
 */
return new class extends Migration
{
    private array $permisos = [
        // ── Despacho a Ventas (lado Preparación) ──────────────────────────
        ['slug' => 'prep.despachos.ver',      'descripcion' => 'Ver despachos a Ventas (Preparación)'],
        ['slug' => 'prep.despachos.crear',    'descripcion' => 'Crear despachos a Ventas'],
        ['slug' => 'prep.despachos.cancelar', 'descripcion' => 'Cancelar despachos a Ventas'],

        // ── Módulo Ventas ──────────────────────────────────────────────────
        ['slug' => 'modulo.ventas',           'descripcion' => 'Acceso al módulo Ventas'],
        ['slug' => 'ventas.dashboard.ver',    'descripcion' => 'Ver dashboard de Ventas'],
        ['slug' => 'ventas.despachos.ver',    'descripcion' => 'Ver despachos entrantes'],
        ['slug' => 'ventas.despachos.aprobar','descripcion' => 'Aprobar/Rechazar despachos'],
        ['slug' => 'ventas.catalogo.ver',     'descripcion' => 'Ver catálogo de equipos en Ventas'],
        ['slug' => 'ventas.catalogo.vender',  'descripcion' => 'Iniciar proceso de venta'],
        ['slug' => 'ventas.apartados.ver',    'descripcion' => 'Ver apartados'],
        ['slug' => 'ventas.apartados.crear',  'descripcion' => 'Crear apartados'],
        ['slug' => 'ventas.apartados.cancelar','descripcion'=> 'Cancelar apartados'],
        ['slug' => 'ventas.clientes.ver',     'descripcion' => 'Ver clientes'],
        ['slug' => 'ventas.clientes.crear',   'descripcion' => 'Crear clientes'],
        ['slug' => 'ventas.clientes.editar',  'descripcion' => 'Editar clientes'],
        ['slug' => 'ventas.historial.ver',    'descripcion' => 'Ver historial de ventas'],
        ['slug' => 'ventas.facturas.ver',     'descripcion' => 'Ver documentos de venta'],
        ['slug' => 'ventas.facturas.emitir',  'descripcion' => 'Emitir facturas/notas de venta'],
        ['slug' => 'ventas.garantias.ver',    'descripcion' => 'Ver garantías de cliente'],
        ['slug' => 'ventas.garantias.recibir','descripcion' => 'Recibir garantías de cliente'],
        ['slug' => 'ventas.garantias.gestionar','descripcion'=> 'Gestionar estatus de garantías'],
        ['slug' => 'ventas.movimientos.ver',  'descripcion' => 'Ver movimientos entre almacenes Ventas'],
        ['slug' => 'ventas.movimientos.crear','descripcion' => 'Mover equipos entre almacenes Ventas'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->permisos as $permiso) {
            $existe = DB::table('permisos')->where('slug', $permiso['slug'])->exists();
            if (! $existe) {
                DB::table('permisos')->insert([
                    'slug'        => $permiso['slug'],
                    'descripcion' => $permiso['descripcion'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        // Asignar todos los permisos de ventas a roles 'ceo' y 'gerente'
        $roles_slugs = ['ceo', 'gerente'];
        
        foreach ($roles_slugs as $slug) {
            $rolId = DB::table('roles')->where('slug', $slug)->value('id');
            if ($rolId) {
                foreach ($this->permisos as $permiso) {
                    $permisoId = DB::table('permisos')->where('slug', $permiso['slug'])->value('id');
                    if ($permisoId) {
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
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->permisos, 'slug');
        $ids = DB::table('permisos')->whereIn('slug', $slugs)->pluck('id');

        DB::table('rol_permiso')->whereIn('permiso_id', $ids)->delete();
        DB::table('usuario_permiso')->whereIn('permiso_id', $ids)->delete();
        DB::table('permisos')->whereIn('id', $ids)->delete();
    }
};
