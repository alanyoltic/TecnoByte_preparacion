<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = \DB::table('permisos')->where('slug', 'like', '%garant%')->pluck('slug');
echo json_encode($perms, JSON_PRETTY_PRINT);
