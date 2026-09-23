<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\CompraInventarioItem::where('compra_inventario_id', '>=', 11)->delete();
App\Models\CompraInventario::where('id', '>=', 11)->delete();
echo 'Deleted ghost orders';
