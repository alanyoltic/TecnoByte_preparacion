<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::whereHas('role', function($q) { $q->where('slug', 'gerente'); })->first();
if ($u) {
    echo "Depto: " . ($u->departamento->clave ?? 'NULL DEPT');
} else {
    echo "NO GERENTE FOUND";
}
