<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\Roles::where('slug', 'gerente')->first();
if ($r) {
    $permisos = \DB::table('rol_permiso')
        ->join('permisos', 'permisos.id', '=', 'rol_permiso.permiso_id')
        ->where('rol_permiso.rol_id', $r->id)
        ->pluck('permisos.slug')->toArray();
    echo json_encode($permisos, JSON_PRETTY_PRINT);
} else {
    echo "NO ROLE FOUND";
}
