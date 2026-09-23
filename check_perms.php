<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\Roles::where('slug', 'gerente')->first();
if ($r) {
    echo json_encode($r->permisos->pluck('nombre'), JSON_PRETTY_PRINT);
} else {
    echo "NO ROLE FOUND";
}
