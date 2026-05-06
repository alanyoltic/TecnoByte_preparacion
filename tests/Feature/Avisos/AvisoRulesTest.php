<?php

use App\Models\Aviso;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function crearAviso(array $attrs = []): Aviso
{
    return Aviso::create(array_merge([
        'titulo' => 'Aviso',
        'texto' => 'Contenido breve',
        'tag' => 'INFO',
        'color' => 'slate',
        'is_active' => true,
        'pinned' => false,
        'starts_at' => null,
        'ends_at' => null,
        'created_by' => null,
    ], $attrs));
}

test('scope activos solo incluye avisos vigentes y activos', function () {
    Carbon::setTestNow('2026-04-29 12:00:00');

    $visible = crearAviso([
        'titulo' => 'Visible',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);
    crearAviso([
        'titulo' => 'Programado',
        'starts_at' => now()->addHour(),
    ]);
    crearAviso([
        'titulo' => 'Expirado',
        'ends_at' => now()->subHour(),
    ]);
    crearAviso([
        'titulo' => 'Inactivo',
        'is_active' => false,
    ]);

    $ids = Aviso::query()->activos()->pluck('id')->all();

    expect($ids)->toBe([$visible->id]);
});

test('canActivate bloquea publicación al alcanzar tope de vigentes', function () {
    Carbon::setTestNow('2026-04-29 12:00:00');

    for ($i = 0; $i < Aviso::MAX_ACTIVE_VISIBLE; $i++) {
        crearAviso([
            'titulo' => "Aviso {$i}",
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
    }

    expect(Aviso::activeVisibleCount())->toBe(Aviso::MAX_ACTIVE_VISIBLE)
        ->and(Aviso::canActivate())->toBeFalse();
});

test('canActivate con ignoreId permite editar aviso vigente existente', function () {
    Carbon::setTestNow('2026-04-29 12:00:00');

    $first = null;
    for ($i = 0; $i < Aviso::MAX_ACTIVE_VISIBLE; $i++) {
        $created = crearAviso([
            'titulo' => "Aviso {$i}",
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        $first ??= $created;
    }

    expect(Aviso::canActivate())->toBeFalse()
        ->and(Aviso::canActivate($first->id))->toBeTrue();
});

test('ordenDashboard prioriza pinned y fecha de inicio', function () {
    Carbon::setTestNow('2026-04-29 12:00:00');

    $normal = crearAviso([
        'titulo' => 'Normal',
        'pinned' => false,
        'starts_at' => now()->subHours(2),
    ]);
    $pinnedOld = crearAviso([
        'titulo' => 'Pinned old',
        'pinned' => true,
        'starts_at' => now()->subHours(5),
    ]);
    $pinnedNew = crearAviso([
        'titulo' => 'Pinned new',
        'pinned' => true,
        'starts_at' => now()->subHour(),
    ]);

    $ordered = Aviso::query()->ordenDashboard()->pluck('id')->take(3)->values()->all();

    expect($ordered)->toBe([$pinnedNew->id, $pinnedOld->id, $normal->id]);
    expect(Aviso::DASHBOARD_LIMIT)->toBe(5);
});
