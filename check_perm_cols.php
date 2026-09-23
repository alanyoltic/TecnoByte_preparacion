<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo json_encode(\Illuminate\Support\Facades\Schema::getColumnListing('permisos'), JSON_PRETTY_PRINT);
