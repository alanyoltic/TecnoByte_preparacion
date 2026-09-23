<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$last = App\Models\CompraInventario::orderBy('id', 'desc')->take(1)->first();
echo $last ? $last->id . ' - ' . $last->estatus : 'None';
