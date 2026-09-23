$roles = \Spatie\Permission\Models\Role::all();
foreach($roles as $r) {
    if(str_contains(strtolower($r->name), 'ventas') || str_contains(strtolower($r->name), 'admin') || str_contains(strtolower($r->name), 'ceo') || str_contains(strtolower($r->name), 'preparacion')) {
        try { $r->givePermissionTo('ventas.compras.ver'); } catch(\Exception $e) {}
        try { $r->givePermissionTo('compras.lotes'); } catch(\Exception $e) {}
    }
}
echo 'Permisos asignados.';
