<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Insertar permisos que faltaban en la base de datos pero existen en rutas/vistas
        $permisosNuevos = [
            ['slug' => 'ventas.caja.ver',                'descripcion' => 'Acceso y consulta de cortes de caja (Ventas)'],
            ['slug' => 'ventas.almacenes.gestion',        'descripcion' => 'Gestión y administración de almacenes de Ventas'],
            ['slug' => 'ventas.compras.ver',              'descripcion' => 'Ver compras y órdenes de insumos (Ventas)'],
            ['slug' => 'prep.compras.ver',                'descripcion' => 'Ver órdenes de compra (Preparación)'],
            ['slug' => 'prep.compras.aprobar_gerente',    'descripcion' => 'Aprobar órdenes de compra nivel Gerente (Preparación)'],
            ['slug' => 'prep.compras.aprobar_ceo',        'descripcion' => 'Aprobar órdenes de compra nivel CEO (Preparación)'],
        ];

        foreach ($permisosNuevos as $p) {
            $existe = DB::table('permisos')->where('slug', $p['slug'])->first();
            if (!$existe) {
                DB::table('permisos')->insert([
                    'slug'        => $p['slug'],
                    'descripcion' => $p['descripcion'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // 2. Asegurar que los roles comerciales existan en la tabla roles
        $rolesVentas = [
            ['id' => 16, 'nombre' => 'Supervisor de Ventas', 'slug' => 'supervisor'],
            ['id' => 17, 'nombre' => 'Asesor de Tienda',     'slug' => 'asesor_tienda'],
            ['id' => 18, 'nombre' => 'Asesor en Línea',      'slug' => 'asesor_linea'],
        ];

        foreach ($rolesVentas as $r) {
            $existe = DB::table('roles')->where('slug', $r['slug'])->first();
            if (!$existe) {
                DB::table('roles')->insert([
                    'id'         => $r['id'],
                    'nombre'     => $r['nombre'],
                    'slug'       => $r['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Mapear y asignar permisos por rol
        $map = DB::table('permisos')->pluck('id', 'slug');

        $asignarPermisos = function(int $rolId, array $slugs) use ($map) {
            foreach ($slugs as $slug) {
                if (isset($map[$slug])) {
                    DB::table('rol_permiso')->insertOrIgnore([
                        'rol_id'     => $rolId,
                        'permiso_id' => $map[$slug],
                    ]);
                }
            }
        };

        // Permisos base de ventas para asesores
        $permisosBaseAsesor = [
            'modulo.ventas',
            'ventas.ver',
            'ventas.pos.ver',
            'ventas.pos.cobrar',
            'ventas.venta.crear',
            'ventas.caja.ver',
            'ventas.clientes.ver',
            'ventas.clientes.crear',
            'ventas.clientes.editar',
            'ventas.catalogo.ver',
            'ventas.catalogo.vender',
            'ventas.apartados.ver',
            'ventas.apartados.crear',
            'ventas.historial.ver',
            'ventas.ventas.ver',
            'ventas.productos.ver',
            'ventas.garantias.ver',
        ];

        // 3a. Asesor de Tienda (agrega recibir garantías de clientes físicos)
        $asignarPermisos(17, array_merge($permisosBaseAsesor, [
            'ventas.garantias.recibir',
        ]));

        // 3b. Asesor en Línea
        $asignarPermisos(18, $permisosBaseAsesor);

        // 3c. Supervisor de Ventas
        $asignarPermisos(16, array_merge($permisosBaseAsesor, [
            'ventas.dashboard.ver',
            'ventas.despachos.ver',
            'ventas.despachos.aprobar',
            'ventas.garantias.recibir',
            'ventas.garantias.gestionar',
            'ventas.apartados.cancelar',
            'ventas.ventas.cancelar',
            'ventas.productos.crear',
            'ventas.productos.editar',
            'ventas.movimientos.ver',
            'ventas.movimientos.crear',
            'ventas.transferencias.ver',
            'ventas.transferencias.crear',
            'ventas.almacenes.gestion',
            'modulo.sistema',
            'sistema.usuarios.ver',
            'sistema.usuarios.crear',
        ]));

        // 3d. Gerente (Rol 2) - Acceso a todos los nuevos permisos
        $asignarPermisos(2, [
            'ventas.caja.ver',
            'ventas.almacenes.gestion',
            'ventas.compras.ver',
            'prep.compras.ver',
            'prep.compras.aprobar_gerente',
        ]);

        // 3e. Admin del Sistema (Rol 12)
        $asignarPermisos(12, [
            'ventas.caja.ver',
            'ventas.almacenes.gestion',
            'ventas.compras.ver',
            'prep.compras.ver',
            'prep.compras.aprobar_gerente',
        ]);

        // 3f. CEO (Rol 1)
        $asignarPermisos(1, [
            'ventas.caja.ver',
            'ventas.almacenes.gestion',
            'ventas.compras.ver',
            'prep.compras.ver',
            'prep.compras.aprobar_gerente',
            'prep.compras.aprobar_ceo',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo en producción
    }
};
