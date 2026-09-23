<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
echo "VENTAS:\n" . json_encode(Schema::getColumnListing('ventas'), JSON_PRETTY_PRINT) . "\n";
echo "VENTA_DETALLES:\n" . json_encode(Schema::getColumnListing('venta_detalles'), JSON_PRETTY_PRINT) . "\n";
echo "DESPACHO_VENTAS:\n" . json_encode(Schema::getColumnListing('despacho_ventas'), JSON_PRETTY_PRINT) . "\n";
echo "TRANSFERENCIAS:\n" . json_encode(Schema::getColumnListing('transferencias'), JSON_PRETTY_PRINT) . "\n";
