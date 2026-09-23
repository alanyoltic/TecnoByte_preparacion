<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo 'CatalogoPieza: ' . implode(',', Schema::getColumnListing((new App\Models\CatalogoPieza)->getTable())) . "\n";
echo 'Producto: ' . implode(',', Schema::getColumnListing((new App\Models\Producto)->getTable())) . "\n";
echo 'Consumible: ' . implode(',', Schema::getColumnListing((new App\Models\Consumible)->getTable())) . "\n";
echo 'Cargador: ' . implode(',', Schema::getColumnListing((new App\Models\Cargador)->getTable())) . "\n";
