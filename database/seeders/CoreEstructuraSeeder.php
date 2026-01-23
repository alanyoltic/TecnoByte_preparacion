<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreEstructuraSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ===== AREAS =====
            $areas = [
                ['clave' => 'PREPARACION', 'nombre' => 'Preparación'],
                ['clave' => 'VENTAS',      'nombre' => 'Ventas'],
                ['clave' => 'SOPORTE',     'nombre' => 'Soporte Técnico'],
                ['clave' => 'RRHH',        'nombre' => 'Recursos Humanos'],
                ['clave' => 'ADMIN',       'nombre' => 'Administración'],
            ];

            foreach ($areas as $a) {
                DB::table('areas')->updateOrInsert(
                    ['clave' => $a['clave']],
                    ['nombre' => $a['nombre'], 'activo' => 1, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            $prepId   = DB::table('areas')->where('clave', 'PREPARACION')->value('id');
            $ventasId = DB::table('areas')->where('clave', 'VENTAS')->value('id');

            // ===== SUCURSALES =====
            $sucursales = [
                ['clave' => 'QRO',    'nombre' => 'Querétaro Corregidora', 'es_virtual' => 0],
                ['clave' => 'LEON',   'nombre' => 'León Guanajuato',       'es_virtual' => 0],
                ['clave' => 'ONLINE', 'nombre' => 'Online Central',        'es_virtual' => 1],
            ];

            foreach ($sucursales as $s) {
                DB::table('sucursales')->updateOrInsert(
                    ['clave' => $s['clave']],
                    ['nombre' => $s['nombre'], 'es_virtual' => $s['es_virtual'], 'activo' => 1, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            $qroId  = DB::table('sucursales')->where('clave', 'QRO')->value('id');
            $leonId = DB::table('sucursales')->where('clave', 'LEON')->value('id');
            $onId   = DB::table('sucursales')->where('clave', 'ONLINE')->value('id');

            // ===== ALMACENES =====
            $almacenes = [
                // QRO - Preparación
                ['clave' => 'QRO_CEDIS_PREP',        'sucursal_id' => $qroId,  'area_id' => $prepId,   'tipo' => 'AREA',  'nombre' => 'CEDIS / Preparación'],
                ['clave' => 'QRO_PIEZAS_PENDIENTES', 'sucursal_id' => $qroId,  'area_id' => $prepId,   'tipo' => 'AREA',  'nombre' => 'Piezas pendientes (Preparación)'],
                ['clave' => 'QRO_QA_LISTOS',         'sucursal_id' => $qroId,  'area_id' => $prepId,   'tipo' => 'AREA',  'nombre' => 'Equipos listos / QA (Preparación)'],

                // QRO - Ventas
                ['clave' => 'QRO_VENTAS_PISO',       'sucursal_id' => $qroId,  'area_id' => $ventasId, 'tipo' => 'AREA',  'nombre' => 'Ventas / Piso de venta'],

                // LEON - Ventas (por ahora)
                ['clave' => 'LEON_VENTAS_PISO',      'sucursal_id' => $leonId, 'area_id' => $ventasId, 'tipo' => 'AREA',  'nombre' => 'Ventas / Piso de venta'],

                // ONLINE - Ventas
                ['clave' => 'ONLINE_CENTRAL',        'sucursal_id' => $onId,   'area_id' => $ventasId, 'tipo' => 'ONLINE','nombre' => 'Online Central'],
            ];

            foreach ($almacenes as $a) {
                DB::table('almacenes')->updateOrInsert(
                    ['clave' => $a['clave']],
                    [
                        'sucursal_id' => $a['sucursal_id'],
                        'area_id'     => $a['area_id'],
                        'tipo'        => $a['tipo'],
                        'nombre'      => $a['nombre'],
                        'activo'      => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }

            // ===== DEFAULTS SUAVES PARA DATOS EXISTENTES =====
            // - Lotes: default QRO si está null
            if ($qroId) {
                DB::table('lotes')->whereNull('sucursal_id')->update(['sucursal_id' => $qroId]);
            }

            // - Users: default QRO si está null (area lo asignas tú luego)
            if ($qroId) {
                DB::table('users')->whereNull('sucursal_id')->update(['sucursal_id' => $qroId]);
            }

            // - Equipos: default QRO + CEDIS_PREP si están null
            $cedisId = DB::table('almacenes')->where('clave', 'QRO_CEDIS_PREP')->value('id');
            if ($qroId && $cedisId) {
                DB::table('equipos')->whereNull('sucursal_id')->update(['sucursal_id' => $qroId]);
                DB::table('equipos')->whereNull('almacen_id')->update(['almacen_id' => $cedisId]);
            }
        });
    }
}
