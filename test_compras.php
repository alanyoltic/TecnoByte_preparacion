<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new App\Livewire\Compras\OrdenesCompra();
$c->area = 'PREPARACION';
$c->busqueda = '';
$res = $c->compras();
echo count($res->items());
