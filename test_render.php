<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = app('Livewire\LivewireManager')->test('compras.ordenes-compra')
    ->set('vista', 'lista');

$html = $c->html();
if (strpos($html, '<!-- Tabla -->') !== false) {
    echo "TABLE IS IN HTML.\n";
} else {
    echo "TABLE IS MISSING FROM HTML.\n";
}
file_put_contents('test_html.html', $html);
