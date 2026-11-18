<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Crea los almacenes de Ventas para la sucursal Corregidora (QRO).
 *
 * Estructura:
 *   Departamento VENTAS → departamento.clave = 'VENTAS'
 *   Sucursal QRO        → sucursales.clave = 'QRO'
 *
 * Almacenes creados:
 *   QRO_VEN_CEDIS   → Bodega de recepción de despachos desde Preparación
 *   QRO_VEN_PISO    → Piso de venta / showroom
 *   QRO_VEN_APART   → Almacén de equipos apartados por clientes
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Asegurar que el departamento VENTAS existe
        $deptoVentasId = null;

        if (\Schema::hasTable('departamento')) {
            $deptoVentasId = DB::table('departamento')->where('clave', 'VENTAS')->value('id');

            if (! $deptoVentasId) {
                $deptoVentasId = DB::table('departamento')->insertGetId([
                    'clave'      => 'VENTAS',
                    'nombre'     => 'VENTAS',
                    'activo'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Obtener la sucursal QRO
        $qroId = DB::table('sucursales')->where('clave', 'QRO')->value('id');

        if (! $qroId) {
            $qroId = DB::table('sucursales')->insertGetId([
                'clave'      => 'QRO',
                'nombre'     => 'Querétaro',
                'es_virtual' => false,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Crear los 3 almacenes de Ventas para QRO
        $almacenes = [
            [
                'clave'   => 'QRO_VEN_CEDIS',
                'nombre'  => 'CEDIS Tienda QRO',
                'tipo'    => 'AREA',
            ],
            [
                'clave'   => 'QRO_VEN_PISO',
                'nombre'  => 'Piso de Venta QRO',
                'tipo'    => 'AREA',
            ],
            [
                'clave'   => 'QRO_VEN_APART',
                'nombre'  => 'Apartados QRO',
                'tipo'    => 'AREA',
            ],
        ];

        foreach ($almacenes as $almacen) {
            $existe = DB::table('almacenes')->where('clave', $almacen['clave'])->exists();

            if (! $existe) {
                $data = [
                    'sucursal_id' => $qroId,
                    'tipo'        => $almacen['tipo'],
                    'clave'       => $almacen['clave'],
                    'nombre'      => $almacen['nombre'],
                    'activo'      => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if ($deptoVentasId && \Schema::hasColumn('almacenes', 'departamento_id')) {
                    $data['departamento_id'] = $deptoVentasId;
                }

                DB::table('almacenes')->insert($data);
            }
        }
    }

    public function down(): void
    {
        DB::table('almacenes')->whereIn('clave', [
            'QRO_VEN_CEDIS',
            'QRO_VEN_PISO',
            'QRO_VEN_APART',
        ])->delete();
    }
};
