<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreEstructuraSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // =========================
            // 1) SUCURSALES
            // =========================
            $sucursales = [
                ['clave' => 'cedis',           'nombre' => 'CEDIS',                      'es_virtual' => 0, 'activo' => 1],
                ['clave' => 'pv_qro',          'nombre' => 'PUNTO DE VENTA QUERETARO',    'es_virtual' => 0, 'activo' => 1],
                ['clave' => 'leon',            'nombre' => 'SUCURSAL LEON',              'es_virtual' => 0, 'activo' => 1],
                ['clave' => 'mant_garantias',  'nombre' => 'MANTENIMIENTO Y GARANTIAS',  'es_virtual' => 0, 'activo' => 1],
                ['clave' => 'zibata',          'nombre' => 'ZIBATA',                     'es_virtual' => 0, 'activo' => 1],
            ];

            foreach ($sucursales as $s) {
                DB::table('sucursales')->updateOrInsert(
                    ['clave' => $s['clave']],
                    [
                        'nombre'     => $s['nombre'],
                        'es_virtual' => $s['es_virtual'],
                        'activo'     => $s['activo'],
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            $sucursalId = fn(string $clave) =>
                (int) DB::table('sucursales')->where('clave', $clave)->value('id');

            // =========================
            // 2) DEPARTAMENTOS (antes "areas")
            // =========================
            $departamentos = [
                ['clave' => 'preparacion',       'nombre' => 'PREPARACION',                'activo' => 1],
                ['clave' => 'ventas',            'nombre' => 'VENTAS',                     'activo' => 1],
                ['clave' => 'soporte_garantias', 'nombre' => 'MANTENIMIENTO Y GARANTIAS',   'activo' => 1],
                ['clave' => 'administracion',    'nombre' => 'ADMINISTRACION',             'activo' => 1],
            ];

            foreach ($departamentos as $d) {
                DB::table('departamento')->updateOrInsert(
                    ['clave' => $d['clave']],
                    [
                        'nombre'     => $d['nombre'],
                        'activo'     => $d['activo'],
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            // ✅ ESTE ES EL QUE TE FALTA
            $departamentoId = fn(string $clave) =>
                (int) DB::table('departamento')->where('clave', $clave)->value('id');

            // =========================
            // 3) ALMACENES
            // =========================
            $almacenes = [
                ['clave' => 'piezas_pend_bat_cables', 'nombre' => 'PIEZAS PENDIENTES (BATERIAS Y CABLES)', 'sucursal' => 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'piezas_pend_extras',     'nombre' => 'PIEZAS PENDIENTES (EXTRAS)',           'sucursal' => 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'preparacion_cedis',      'nombre' => 'PREPARACION CEDIS',                   'sucursal' => 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'entradas_cedis',         'nombre' => 'ENTRADAS CEDIS',                      'sucursal' => 'zibata', 'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'stock_cedis',            'nombre' => 'Stock CEDIS',                         'sucursal' => 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'listos_venta_cedis',      'nombre' => 'LISTOS PARA VENTA ALMACENADO EN CEDIS','sucursal'=> 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],
                ['clave' => 'mini_bodega',            'nombre' => 'MINI BODEGA',                         'sucursal' => 'cedis',  'departamento' => 'preparacion',        'tipo' => 'AREA'],

                ['clave' => 'venta_colaboradores',    'nombre' => 'VENTA EQUIPOS COLABORADORES',         'sucursal' => 'cedis',  'departamento' => 'ventas',             'tipo' => 'AREA'],
                ['clave' => 'equipos_usuarios',       'nombre' => 'EQUIPOS USUARIOS TECNOBYTE',          'sucursal' => 'cedis',  'departamento' => 'administracion',     'tipo' => 'AREA'],
                ['clave' => 'administracion',         'nombre' => 'ADMINISTRACION',                      'sucursal' => 'cedis',  'departamento' => 'administracion',     'tipo' => 'AREA'],

                ['clave' => 'tienda_corregidora',      'nombre' => 'TIENDA CORREGIDORA',                  'sucursal' => 'pv_qro', 'departamento' => 'ventas',             'tipo' => 'AREA'],
                ['clave' => 'apartados_pv',            'nombre' => 'APARTADOS PV',                        'sucursal' => 'pv_qro', 'departamento' => 'ventas',             'tipo' => 'AREA'],
                ['clave' => 'corregidora_ventas_linea','nombre' => 'CORREGIDORA VENTAS LINEA',            'sucursal' => 'pv_qro', 'departamento' => 'ventas',             'tipo' => 'ONLINE'],

                ['clave' => 'tienda_leon',             'nombre' => 'TIENDA LEON',                         'sucursal' => 'leon',   'departamento' => 'ventas',             'tipo' => 'AREA'],

                ['clave' => 'refacciones',             'nombre' => 'REFACCIONES',                         'sucursal' => 'mant_garantias', 'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
                ['clave' => 'garantias_posventa',      'nombre' => 'GARANTIAS POSVENTA',                  'sucursal' => 'mant_garantias', 'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
                ['clave' => 'equipos_cambios_garantia','nombre' => 'EQUIPOS CAMBIOS DE GARANTIA',         'sucursal' => 'mant_garantias', 'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
                ['clave' => 'actualizacion_posventa',  'nombre' => 'ACTUALIZACION EQUIPOS POSVENTA',      'sucursal' => 'mant_garantias', 'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],

                ['clave' => 'garantias_proveedores',   'nombre' => 'GARANTIAS PROVEEDORES',               'sucursal' => 'cedis',  'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
                ['clave' => 'reparacion_externa',      'nombre' => 'REPARACION EXTERNA',                  'sucursal' => 'cedis',  'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
                ['clave' => 'ing_reparacion_erick',    'nombre' => 'Ing. Reparación (Erick)',             'sucursal' => 'cedis',  'departamento' => 'soporte_garantias', 'tipo' => 'AREA'],
            ];

            foreach ($almacenes as $a) {
                DB::table('almacenes')->updateOrInsert(
                    ['clave' => $a['clave']],
                    [
                        'nombre'          => $a['nombre'],
                        'tipo'            => $a['tipo'],
                        'sucursal_id'     => $sucursalId($a['sucursal']),
                        'departamento_id' => $departamentoId($a['departamento']),
                        'updated_at'      => now(),
                        'created_at'      => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        });
    }
}
