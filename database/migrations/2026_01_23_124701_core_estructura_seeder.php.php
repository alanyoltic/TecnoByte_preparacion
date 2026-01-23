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
                    ['nombre' => $a['nombre'], 'activo' => 1, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $prepId  = DB::table('areas')->where('clave', 'PREPARACION')->value('id');
            $ventasId= DB::table('areas')->where('clave', 'VENTAS')->value('id');

            // ===== SUCURSALES =====
            $sucursales = [
                ['clave' => 'QRO',    'nombre' => 'Querétaro Corregidora', 'es_virtual' => 0],
                ['clave' => 'LEON',   'nombre' => 'León Guanajuato',       'es_virtual' => 0],
                ['clave' => 'ONLINE', 'nombre' => 'Online Central',        'es_virtual' => 1],
            ];

            foreach ($sucursales as $s) {
                DB::table('sucursales')->updateOrInsert(
                    ['clave' => $s['clave']],
                    ['nombre' => $s['nombre'], 'es_virtual' => $s['es_virtual'], 'activo' => 1, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $qroId   = DB::table('sucursales')->where('clave', 'QRO')->value('id');
            $leonId  = DB::table('sucursales')->where('clave', 'LEON')->value('id');
            $onId    = DB::table('sucursales')->where('clave', 'ONLINE')->value('id');

            // ===== ALMACENES =====
            $almacenes = [
                // QRO - Preparación
                ['clave' => 'QRO_CEDIS_PREP',  'sucursal_id' => $qroId,  'area_id' => $prepId,  'tipo' => 'AREA',  'nombre' => 'CEDIS / Preparación'],
                ['clave' => 'QRO_QA_LISTOS',   'sucursal_id' => $qroId,  'area_id' => $prepId,  'tipo' => 'AREA',  'nombre' => 'Equipos Listos / QA'],

                // QRO - Ventas
                ['clave' => 'QRO_VENTAS_PISO', 'sucursal_id' => $qroId,  'area_id' => $ventasId,'tipo' => 'AREA',  'nombre' => 'Ventas / Piso de venta'],

                // LEON - Preparación
                ['clave' => 'LEON_CEDIS_PREP', 'sucursal_id' => $leonId, 'area_id' => $prepId,  'tipo' => 'AREA',  'nombre' => 'CEDIS / Preparación'],
                ['clave' => 'LEON_QA_LISTOS',  'sucursal_id' => $leonId, 'area_id' => $prepId,  'tipo' => 'AREA',  'nombre' => 'Equipos Listos / QA'],

                // LEON - Ventas
                ['clave' => 'LEON_VENTAS_PISO','sucursal_id' => $leonId, 'area_id' => $ventasId,'tipo' => 'AREA',  'nombre' => 'Ventas / Piso de venta'],

                // ONLINE (virtual) - Ventas
                ['clave' => 'ONLINE_CENTRAL',  'sucursal_id' => $onId,   'area_id' => $ventasId,'tipo' => 'ONLINE','nombre' => 'Online Central'],
            ];

            foreach ($almacenes as $a) {
                if (!$a['sucursal_id']) continue; // safety
                DB::table('almacenes')->updateOrInsert(
                    ['clave' => $a['clave']],
                    [
                        'sucursal_id' => $a['sucursal_id'],
                        'area_id'     => $a['area_id'],
                        'tipo'        => $a['tipo'],
                        'nombre'      => $a['nombre'],
                        'activo'      => 1,
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            }

            // ===== DEFAULTS PARA DATOS EXISTENTES =====

            // Lotes: default QRO si está vacío
            if ($qroId) {
                DB::table('lotes')->whereNull('sucursal_id')->update(['sucursal_id' => $qroId]);
            }

            // Users: default QRO para sucursal (area lo asignas según organigrama)
            if ($qroId) {
                DB::table('users')->whereNull('sucursal_id')->update(['sucursal_id' => $qroId]);
            }
        });
    }
}
