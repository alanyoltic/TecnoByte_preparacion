<?php
use Spatie\Permission\Models\Role;
foreach(Role::all() as \) {
    try { \->givePermissionTo('ventas.compras.ver'); } catch(\Exception \) {}
    try { \->givePermissionTo('prep.compras.ver'); } catch(\Exception \) {}
    try { \->givePermissionTo('ventas.productos.ver'); } catch(\Exception \) {}
    try { \->givePermissionTo('compras.lotes'); } catch(\Exception \) {}
}
echo 'Listo';

