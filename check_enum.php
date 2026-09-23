<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM compras_inventario LIKE "estatus"')[0]->Type;
