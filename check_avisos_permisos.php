<?php

// Validar permisos de avisos por rol

$permisos = ['sistema.avisos.ver', 'sistema.avisos.gestion'];

foreach ($permisos as $slug) {
    echo "\n=== Permiso: $slug ===\n";
    $permiso = DB::table('permisos')->where('slug', $slug)->first();

    if ($permiso) {
        $roles = DB::table('rol_permiso')
            ->join('roles', 'roles.id', '=', 'rol_permiso.rol_id')
            ->where('rol_permiso.permiso_id', $permiso->id)
            ->pluck('roles.nombre')
            ->toArray();

        echo "ID: {$permiso->id}\n";
        echo 'Roles asignados: '.(count($roles) > 0 ? implode(', ', $roles) : 'NINGUNO')."\n";
    } else {
        echo "NO ENCONTRADO\n";
    }
}
