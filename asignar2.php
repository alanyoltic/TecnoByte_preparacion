<?php
use Illuminate\Support\Facades\DB;
use App\Models\Roles;

$permisos = [
    ['slug' => 'ventas.compras.ver'],
    ['slug' => 'prep.compras.ver'],
    ['slug' => 'ventas.productos.ver'],
    ['slug' => 'compras.lotes'],
];

foreach($permisos as $p) {
    try {
        DB::table('permisos')->insertOrIgnore(['slug' => $p['slug']]);
    } catch (\Exception $e) {}
}

$roles = Roles::all();
foreach($roles as $r) {
    $n = strtolower($r->slug ?? $r->name ?? '');
    if(str_contains($n, 'ventas') || str_contains($n, 'admin') || str_contains($n, 'ceo') || str_contains($n, 'preparacion') || str_contains($n, 'gerente')) {
        foreach($permisos as $p) {
            $pid = DB::table('permisos')->where('slug', $p['slug'])->value('id');
            if($pid) {
                try {
                    DB::table('rol_permiso')->insertOrIgnore([
                        'rol_id' => $r->id,
                        'permiso_id' => $pid
                    ]);
                } catch (\Exception $e) {}
            }
        }
    }
}
echo "Listo.\n";
