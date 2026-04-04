<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. LIMPIAR TABLAS (respeta foreign keys)
        // =====================================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('usuario_permiso')->delete();
        DB::table('rol_permiso')->delete();
        DB::table('permisos')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =====================================================================
        // 2. INSERTAR PERMISOS NUEVOS
        // =====================================================================
        $now = now();

        $permisos = [
            // -----------------------------------------------------------------
            // DASHBOARD
            // -----------------------------------------------------------------
            ['slug' => 'sistema.dashboard.ver',          'descripcion' => 'Ver dashboard'],

            // -----------------------------------------------------------------
            // MÓDULOS (controla acceso al menú lateral)
            // -----------------------------------------------------------------
            ['slug' => 'modulo.preparacion',             'descripcion' => 'Acceso al módulo Preparación'],
            ['slug' => 'modulo.ventas',                  'descripcion' => 'Acceso al módulo Ventas'],
            ['slug' => 'modulo.soporte',                 'descripcion' => 'Acceso al módulo Soporte'],
            ['slug' => 'modulo.rrhh',                    'descripcion' => 'Acceso al módulo RRHH'],
            ['slug' => 'modulo.administracion',          'descripcion' => 'Acceso al módulo Administración'],
            ['slug' => 'modulo.sistema',                  'descripcion' => 'Acceso a herramientas del sistema'],

            // -----------------------------------------------------------------
            // PREPARACIÓN — EQUIPOS
            // -----------------------------------------------------------------
            ['slug' => 'prep.equipos.ver',               'descripcion' => 'Ver equipos (Preparación)'],
            ['slug' => 'prep.equipos.crear',             'descripcion' => 'Registrar equipos (Preparación)'],
            ['slug' => 'prep.equipos.editar',            'descripcion' => 'Editar equipos (Preparación)'],
            ['slug' => 'prep.equipos.eliminar',          'descripcion' => 'Eliminar equipos (Preparación)'],
            ['slug' => 'prep.equipos.imprimir',          'descripcion' => 'Imprimir etiquetas (Preparación)'],

            // PREPARACIÓN — LOTES
            ['slug' => 'prep.lotes.ver',                 'descripcion' => 'Ver lotes (Preparación)'],
            ['slug' => 'prep.lotes.gestion',             'descripcion' => 'Gestionar lotes (Preparación)'],

            // PREPARACIÓN — INVENTARIO
            ['slug' => 'prep.inventario.ver',            'descripcion' => 'Ver inventario (Preparación)'],
            ['slug' => 'prep.inventario.gestion',        'descripcion' => 'Gestionar inventario (Preparación)'],

            // PREPARACIÓN — TRANSFERENCIAS
            ['slug' => 'prep.transferencias.ver',        'descripcion' => 'Ver transferencias (Preparación)'],
            ['slug' => 'prep.transferencias.crear',      'descripcion' => 'Crear transferencias (Preparación)'],

            // -----------------------------------------------------------------
            // VENTAS
            // -----------------------------------------------------------------
            ['slug' => 'ventas.ver',                     'descripcion' => 'Ver módulo ventas'],
            ['slug' => 'ventas.venta.crear',             'descripcion' => 'Crear venta / cotización'],
            ['slug' => 'ventas.venta.editar',            'descripcion' => 'Editar venta / cotización'],
            ['slug' => 'ventas.garantia.gestionar',      'descripcion' => 'Gestionar garantías (Ventas)'],
            ['slug' => 'ventas.margen.ver',              'descripcion' => 'Ver margen y costos'],
            ['slug' => 'ventas.reporte.ver',             'descripcion' => 'Ver reportes propios (Ventas)'],
            ['slug' => 'ventas.transferencias.ver',      'descripcion' => 'Ver transferencias (Ventas)'],
            ['slug' => 'ventas.transferencias.crear',    'descripcion' => 'Crear transferencias (Ventas)'],

            // -----------------------------------------------------------------
            // SOPORTE
            // -----------------------------------------------------------------
            ['slug' => 'soporte.ver',                    'descripcion' => 'Ver módulo soporte'],
            ['slug' => 'soporte.ticket.crear',           'descripcion' => 'Crear ticket / servicio'],
            ['slug' => 'soporte.ticket.editar',          'descripcion' => 'Editar ticket / servicio'],
            ['slug' => 'soporte.ticket.cerrar',          'descripcion' => 'Cerrar ticket / servicio'],
            ['slug' => 'soporte.garantia.validar',       'descripcion' => 'Validar garantías (Soporte)'],
            ['slug' => 'soporte.cobro.crear',            'descripcion' => 'Registrar cobro (Soporte)'],
            ['slug' => 'soporte.transferencias.ver',     'descripcion' => 'Ver transferencias (Soporte)'],

            // -----------------------------------------------------------------
            // SISTEMA (transversal)
            // -----------------------------------------------------------------
            ['slug' => 'sistema.usuarios.ver',           'descripcion' => 'Ver usuarios'],
            ['slug' => 'sistema.usuarios.crear',         'descripcion' => 'Crear usuarios'],
            ['slug' => 'sistema.usuarios.editar',        'descripcion' => 'Editar usuarios'],
            ['slug' => 'sistema.avisos.ver',             'descripcion' => 'Ver avisos / anuncios'],
            ['slug' => 'sistema.reportes.ver',           'descripcion' => 'Ver reportes globales'],
            ['slug' => 'sistema.reportes.exportar',      'descripcion' => 'Exportar reportes'],
            ['slug' => 'sistema.contabilidad.ver',       'descripcion' => 'Ver resumen contable'],
            ['slug' => 'sistema.admin.roles',            'descripcion' => 'Gestionar roles y permisos'],
            ['slug' => 'sistema.admin.configuracion',    'descripcion' => 'Configuración general del sistema'],
        ];

        // Insertar y construir mapa slug → id
        $mapaIds = [];
        foreach ($permisos as $permiso) {
            $id = DB::table('permisos')->insertGetId([
                'slug'       => $permiso['slug'],
                'descripcion' => $permiso['descripcion'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $mapaIds[$permiso['slug']] = $id;
        }

        // =====================================================================
        // 3. OBTENER IDs DE ROLES
        // =====================================================================
        $roles = DB::table('roles')->pluck('id', 'slug');

        // =====================================================================
        // 4. MATRIZ ROL → PERMISOS
        // =====================================================================

        // CEO — todo
        $ceo = array_values($mapaIds);

        // Admin Sistema — todo excepto no lo diferenciamos del CEO en permisos
        $adminSistema = array_values($mapaIds);

        // Gerente — todo excepto admin.roles y admin.configuracion
        $gerente = $this->slugsAIds($mapaIds, [
            'sistema.dashboard.ver',
            'modulo.preparacion',
            'modulo.ventas',
            'modulo.soporte',
            'prep.equipos.ver',
            'prep.equipos.crear',
            'prep.equipos.editar',
            'prep.equipos.eliminar',
            'prep.equipos.imprimir',
            'prep.lotes.ver',
            'prep.lotes.gestion',
            'prep.inventario.ver',
            'prep.inventario.gestion',
            'prep.transferencias.ver',
            'prep.transferencias.crear',
            'ventas.ver',
            'ventas.venta.crear',
            'ventas.venta.editar',
            'ventas.garantia.gestionar',
            'ventas.margen.ver',
            'ventas.reporte.ver',
            'ventas.transferencias.ver',
            'ventas.transferencias.crear',
            'soporte.ver',
            'soporte.ticket.crear',
            'soporte.ticket.editar',
            'soporte.ticket.cerrar',
            'soporte.garantia.validar',
            'soporte.cobro.crear',
            'soporte.transferencias.ver',
            'sistema.usuarios.ver',
            'sistema.usuarios.crear',
            'sistema.usuarios.editar',
            'sistema.reportes.ver',
            'sistema.reportes.exportar',
            'sistema.contabilidad.ver',
            'modulo.sistema',
        ]);

        // Lider — operativo + administrativo de su área, sin reportes globales ni contabilidad
        $lider = $this->slugsAIds($mapaIds, [
            'sistema.dashboard.ver',
            'modulo.preparacion',
            'prep.equipos.ver',
            'prep.equipos.crear',
            'prep.equipos.editar',
            'prep.equipos.eliminar',
            'prep.equipos.imprimir',
            'prep.lotes.ver',
            'prep.lotes.gestion',
            'prep.inventario.ver',
            'prep.inventario.gestion',
            'prep.transferencias.ver',
            'prep.transferencias.crear',
            'sistema.usuarios.ver',
            'sistema.usuarios.crear',
            'sistema.usuarios.editar',
            'modulo.sistema',
        ]);

        // Tecnico — solo operativo básico de su área
        $tecnico = $this->slugsAIds($mapaIds, [
            'sistema.dashboard.ver',
            'modulo.preparacion',
            'prep.equipos.ver',
            'prep.equipos.crear',
            'prep.equipos.imprimir',
            'prep.inventario.ver',
            'modulo.sistema',
        ]);

        // Exhibicion — solo dashboard
        $exhibicion = $this->slugsAIds($mapaIds, [
            'sistema.dashboard.ver',
        ]);

        // Usuario personalizado — sin permisos base (se asignan por usuario_permiso)
        $usuarioPersonalizado = [];

        // =====================================================================
        // 5. INSERTAR ROL_PERMISO
        // =====================================================================
        $matriz = [
            'ceo'          => $ceo,
            'admin_sistema'=> $adminSistema,
            'gerente'      => $gerente,
            'lider'        => $lider,
            'tecnico'      => $tecnico,
            'exhibicion'   => $exhibicion,
            'usuario'      => $usuarioPersonalizado,
        ];

        $inserts = [];
        foreach ($matriz as $rolSlug => $permisosIds) {
            if (!isset($roles[$rolSlug])) {
                $this->command->warn("Rol [{$rolSlug}] no encontrado en BD, se omite.");
                continue;
            }
            $rolId = $roles[$rolSlug];
            foreach ($permisosIds as $permisoId) {
                $inserts[] = [
                    'rol_id'     => $rolId,
                    'permiso_id' => $permisoId,
                ];
            }
        }

        // Insertar en chunks para no saturar
        foreach (array_chunk($inserts, 100) as $chunk) {
            DB::table('rol_permiso')->insert($chunk);
        }

        $this->command->info('Permisos y rol_permiso generados correctamente.');
        $this->command->info('Total permisos: ' . count($permisos));
        $this->command->info('Total asignaciones: ' . count($inserts));
    }

    // =========================================================
    // HELPER
    // =========================================================
    private function slugsAIds(array $mapa, array $slugs): array
    {
        return array_values(array_filter(
            array_map(fn($slug) => $mapa[$slug] ?? null, $slugs),
            fn($id) => $id !== null
        ));
    }
}
