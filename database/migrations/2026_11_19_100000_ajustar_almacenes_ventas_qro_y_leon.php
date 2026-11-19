<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reemplazar índice unique global de almacenes.clave por compuesto (sucursal_id, clave)
        $indices = DB::select("SHOW INDEX FROM almacenes WHERE Key_name = 'almacenes_clave_unique'");
        if (!empty($indices)) {
            Schema::table('almacenes', function (Blueprint $table) {
                $table->dropUnique('almacenes_clave_unique');
            });
        }

        $indiceCompuesto = DB::select("SHOW INDEX FROM almacenes WHERE Key_name = 'almacenes_sucursal_clave_unique'");
        if (empty($indiceCompuesto)) {
            Schema::table('almacenes', function (Blueprint $table) {
                $table->unique(['sucursal_id', 'clave'], 'almacenes_sucursal_clave_unique');
            });
        }

        // 2. Resolver departamento VENTAS
        $deptoVentasId = DB::table('departamento')->where('clave', 'VENTAS')->value('id');
        if (!$deptoVentasId) {
            $deptoVentasId = DB::table('departamento')->insertGetId([
                'clave'      => 'VENTAS',
                'nombre'     => 'VENTAS',
                'activo'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Resolver Sucursal Querétaro (QRO_CORREGIDORA)
        $qro = DB::table('sucursales')
            ->where('clave', 'QRO_CORREGIDORA')
            ->orWhere('clave', 'QRO')
            ->first();

        $qroId = null;
        if ($qro) {
            $qroId = $qro->id;
            DB::table('sucursales')->where('id', $qroId)->update([
                'clave'     => 'QRO_CORREGIDORA',
                'nombre'    => 'Querétaro Corregidora',
                'ciudad'    => 'Corregidora',
                'estado'    => 'Querétaro',
                'es_matriz' => true,
                'activo'    => true,
            ]);
        } else {
            $qroId = DB::table('sucursales')->insertGetId([
                'clave'      => 'QRO_CORREGIDORA',
                'nombre'     => 'Querétaro Corregidora',
                'ciudad'     => 'Corregidora',
                'estado'     => 'Querétaro',
                'es_virtual' => false,
                'es_matriz'  => true,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Resolver Sucursal León (LEON)
        $leon = DB::table('sucursales')->where('clave', 'LEON')->first();
        $leonId = null;
        if ($leon) {
            $leonId = $leon->id;
            DB::table('sucursales')->where('id', $leonId)->update([
                'nombre'    => 'León',
                'ciudad'    => 'León',
                'estado'    => 'Guanajuato',
                'es_matriz' => false,
                'activo'    => true,
            ]);
        } else {
            $leonId = DB::table('sucursales')->insertGetId([
                'clave'      => 'LEON',
                'nombre'     => 'León',
                'ciudad'     => 'León',
                'estado'     => 'Guanajuato',
                'es_virtual' => false,
                'es_matriz'  => false,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Eliminar almacenes viejos de ventas que ya no se usan
        $clavesViejas = ['QRO_VEN_CEDIS', 'QRO_VEN_PISO', 'QRO_VEN_APART', 'TIENDA_QRO', 'BODEGA_TIENDA_QRO', 'APARTADOS_QRO'];
        DB::table('almacenes')
            ->whereIn('clave', $clavesViejas)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('equipos')->whereColumn('equipos.almacen_id', 'almacenes.id');
            })
            ->delete();

        // 6. Asegurar Almacenes para Querétaro
        $almacenesQro = [
            ['clave' => 'Piso', 'nombre' => 'Piso de venta Qro'],
            ['clave' => 'Bode', 'nombre' => 'Bodega tienda Qro'],
            ['clave' => 'Apar', 'nombre' => 'Apartados Qro'],
        ];

        foreach ($almacenesQro as $alm) {
            $existe = DB::table('almacenes')
                ->where('sucursal_id', $qroId)
                ->where('clave', $alm['clave'])
                ->first();

            if ($existe) {
                DB::table('almacenes')->where('id', $existe->id)->update([
                    'nombre'          => $alm['nombre'],
                    'departamento_id' => $deptoVentasId,
                    'tipo'            => 'AREA',
                    'activo'          => true,
                    'updated_at'      => now(),
                ]);
            } else {
                DB::table('almacenes')->insert([
                    'sucursal_id'     => $qroId,
                    'departamento_id' => $deptoVentasId,
                    'tipo'            => 'AREA',
                    'clave'           => $alm['clave'],
                    'nombre'          => $alm['nombre'],
                    'activo'          => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 7. Asegurar Almacenes para León
        $almacenesLeon = [
            ['clave' => 'Piso', 'nombre' => 'Piso de venta Leon'],
            ['clave' => 'Bode', 'nombre' => 'Bodega tienda Leon'],
            ['clave' => 'Apar', 'nombre' => 'Apartados Leon'],
        ];

        foreach ($almacenesLeon as $alm) {
            $existe = DB::table('almacenes')
                ->where('sucursal_id', $leonId)
                ->where('clave', $alm['clave'])
                ->first();

            if ($existe) {
                DB::table('almacenes')->where('id', $existe->id)->update([
                    'nombre'          => $alm['nombre'],
                    'departamento_id' => $deptoVentasId,
                    'tipo'            => 'AREA',
                    'activo'          => true,
                    'updated_at'      => now(),
                ]);
            } else {
                DB::table('almacenes')->insert([
                    'sucursal_id'     => $leonId,
                    'departamento_id' => $deptoVentasId,
                    'tipo'            => 'AREA',
                    'clave'           => $alm['clave'],
                    'nombre'          => $alm['nombre'],
                    'activo'          => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo para producción
    }
};
