<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuestosSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // 1) PUESTOS (CATÁLOGO)
        // =========================
        $puestos = [
            ['clave' => 'TECNICO',         'nombre' => 'Tecnico',          'activo' => 1],
            ['clave' => 'VENDEDOR',        'nombre' => 'Vendedor',         'activo' => 1],
            ['clave' => 'MARKETING',       'nombre' => 'Marketing',        'activo' => 1],
            ['clave' => 'RRHH',            'nombre' => 'Recursos Humanos', 'activo' => 1],
            ['clave' => 'ADMINISTRACION',  'nombre' => 'Administracion',   'activo' => 1],
            ['clave' => 'DIRECCION',       'nombre' => 'Direccion',        'activo' => 1],
        ];

        foreach ($puestos as $p) {
            DB::table('puestos')->updateOrInsert(
                ['clave' => $p['clave']],
                [
                    'nombre'     => $p['nombre'],
                    'activo'     => (int) $p['activo'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        $puestoId = fn (string $clave) => (int) DB::table('puestos')->where('clave', $clave)->value('id');

        // =========================
        // 2) RELACIÓN DEPTO ↔ PUESTOS
        // =========================
        // Nota: tú dijiste que MARKETING puede existir en el futuro.
        // Si hoy no existe en tu tabla departamento, simplemente no se vincula.
        $depId = fn (string $clave) => DB::table('departamento')->where('clave', $clave)->value('id');

        $map = [
            'PREPARACION'     => ['TECNICO'],
            'SOPORTE'         => ['TECNICO'],
            'VENTAS'          => ['VENDEDOR'],
            'RRHH'            => ['RRHH'],
            'ADMIN'           => ['ADMINISTRACION'],
            'ADMINISTRACION'  => ['ADMINISTRACION'],
            'MARKETING'       => ['MARKETING'],
        ];

        foreach ($map as $depClave => $puestosClaves) {
            $departamentoId = $depId($depClave);

            if (! $departamentoId) {
                continue; // el departamento no existe aún, no pasa nada
            }

            foreach ($puestosClaves as $pClave) {
                $pId = $puestoId($pClave);

                DB::table('departamento_puestos')->updateOrInsert(
                    [
                        'departamento_id' => $departamentoId,
                        'puesto_id'       => $pId,
                    ],
                    [
                        'activo'     => 1,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        }

        // =========================
        // 3) BACKFILL USERS (SUAVE)
        // =========================
        // Regla profesional mínima:
        // - Si ya tiene puesto_id, respetarlo.
        // - Si no tiene puesto_id:
        //    PREPARACION/SOPORTE + role=tecnico => TECNICO
        //    VENTAS => VENDEDOR
        //    RRHH => RRHH
        //    ADMIN/ADMINISTRACION => ADMINISTRACION
        //    CEO => DIRECCION (opcional)

        $roleSlugById = DB::table('roles')->pluck('slug', 'id'); // [id => slug]
        $depClaveById = DB::table('departamento')->pluck('clave', 'id'); // [id => clave]

        $users = DB::table('users')->select('id', 'role_id', 'departamento_id', 'puesto_id')->get();

        foreach ($users as $u) {
            if (! empty($u->puesto_id)) {
                continue;
            }

            $roleSlug = strtoupper((string) ($roleSlugById[$u->role_id] ?? ''));
            $depClave = strtoupper((string) ($depClaveById[$u->departamento_id] ?? ''));

            $target = null;

            if ($roleSlug === 'CEO') {
                $target = 'DIRECCION';
            } elseif (in_array($depClave, ['PREPARACION', 'SOPORTE'], true) && in_array($roleSlug, ['TECNICO', 'TÉCNICO'], true)) {
                $target = 'TECNICO';
            } elseif ($depClave === 'VENTAS') {
                $target = 'VENDEDOR';
            } elseif ($depClave === 'RRHH') {
                $target = 'RRHH';
            } elseif (in_array($depClave, ['ADMIN', 'ADMINISTRACION'], true)) {
                $target = 'ADMINISTRACION';
            }

            if ($target) {
                DB::table('users')->where('id', $u->id)->update([
                    'puesto_id'   => $puestoId($target),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
