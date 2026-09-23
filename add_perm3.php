<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = \DB::table('permisos')->insertGetId([
    'slug' => 'prep.garantias.ver',
    'descripcion' => 'Ver listado de garantias externas',
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "Created $id\n";

$roles = \DB::table('roles')->whereIn('slug', ['gerente', 'lider'])->get();
foreach ($roles as $r) {
    \DB::table('rol_permiso')->insert([
        'rol_id' => $r->id,
        'permiso_id' => $id,
    ]);
    echo "Added to $r->slug\n";
}
