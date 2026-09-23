<?php
use Illuminate\Support\Facades\DB;
use App\Models\Roles;

$permisos = [
    ['slug' => 'ventas.compras.ver', 'nombre' => 'Ver compras ventas'],
    ['slug' => 'prep.compras.ver', 'nombre' => 'Ver compras prep'],
    ['slug' => 'ventas.productos.ver', 'nombre' => 'Ver catalogo productos'],
    ['slug' => 'compras.lotes', 'nombre' => 'Ver lotes compras'],
];

foreach($permisos as $p) {
    DB::table('permisos')->updateOrInsert(['slug' => $p['slug']], [
        'nombre' => $p['nombre'],
        'descripcion' => 'Auto-generado por script',
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

$roles = Roles::all();
foreach($roles as $r) {
    $n = strtolower($r->slug ?? $r->name ?? '');
    if(str_contains($n, 'ventas') || str_contains($n, 'admin') || str_contains($n, 'ceo') || str_contains($n, 'preparacion') || str_contains($n, 'gerente')) {
        foreach($permisos as $p) {
            $pid = DB::table('permisos')->where('slug', $p['slug'])->value('id');
            if($pid) {
                DB::table('rol_permiso')->updateOrInsert([
                    'rol_id' => $r->id,
                    'permiso_id' => $pid
                ]);
            }
        }
    }
}
echo "Permisos inyectados en BD de permisos.\n";
