<?php

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('id', 12)->first();
$rol = DB::table('roles')->where('id', $user->role_id)->first();

echo "\n=== VERIFICACION USUARIO PANTALLA ===\n";
echo "Usuario: {$user->nombre}\n";
echo "Email: {$user->email}\n";
echo "Rol: {$rol->nombre} ({$rol->slug})\n\n";

$permisos = DB::table('rol_permiso')
    ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
    ->where('rol_permiso.rol_id', $user->role_id)
    ->pluck('permisos.slug')
    ->toArray();

echo 'Permisos del rol ('.count($permisos)."):\n";
foreach ($permisos as $p) {
    $marker = $p === 'modulo.preparacion' ? '[✅]' : '    ';
    echo "$marker $p\n";
}

$tienePermiso = in_array('modulo.preparacion', $permisos);
echo "\n".($tienePermiso ? "✅ CORRECTO: Tiene 'modulo.preparacion'\n" : "❌ ERROR: NO tiene 'modulo.preparacion'\n");
echo "=== FIN ===\n";
