<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use App\Models\EquipoGpu;

class BackfillEquipoGpus extends Command
{
    protected $signature = 'tecnobyte:backfill-equipo-gpus {--dry : Solo simula, no escribe} {--only= : INTEGRADA o DEDICADA}';
    protected $description = 'Migra campos legacy (equipos.grafica_*) hacia equipo_gpus usando updateOrCreate.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $only = strtoupper(trim((string) $this->option('only')));
        if (!in_array($only, ['', 'INTEGRADA', 'DEDICADA'], true)) {
            $this->error("--only debe ser INTEGRADA o DEDICADA");
            return self::FAILURE;
        }

        $this->info('Backfill equipo_gpus ' . ($dry ? '(DRY)' : '(WRITE)') . ($only ? " only={$only}" : ''));

        $total = 0; $created = 0; $updated = 0; $deleted = 0;

        $normText = function ($v): ?string {
            $v = is_string($v) ? trim($v) : $v;
            if ($v === null) return null;
            if ($v === '') return null;
            $up = strtoupper((string)$v);
            if (in_array($up, ['NO', 'N/A', 'NA', 'NULL', 'SIN', '0'], true)) return null;
            return (string) $v;
        };

        $inferMarca = function (?string $modelo): ?string {
            if (!$modelo) return null;
            $u = strtoupper($modelo);
            if (str_contains($u, 'NVIDIA') || str_contains($u, 'GEFORCE') || str_contains($u, 'RTX') || str_contains($u, 'GTX') || str_contains($u, 'QUADRO')) return 'NVIDIA';
            if (str_contains($u, 'AMD') || str_contains($u, 'RADEON') || str_contains($u, 'VEGA')) return 'AMD';
            if (str_contains($u, 'INTEL') || str_contains($u, 'IRIS') || str_contains($u, 'UHD')) return 'INTEL';
            return null;
        };

        // vram puede ser int (ej 4) o string (ej "4096MB", "4GB")
        $parseVram = function ($raw): array {
    $raw = is_string($raw) ? trim($raw) : $raw;

    if ($raw === null || $raw === '') return [null, null, null];

    $up = strtoupper((string)$raw);
    if (in_array($up, ['NO', 'N/A', 'NA', 'NULL', 'SIN', '0'], true)) return [null, null, null];

    // Numérico puro -> GB por defecto
    if (is_numeric($raw)) {
        $n = (int) $raw;
        return $n > 0 ? [$n, 'GB', null] : [null, null, null];
    }

    // Formatos tipo: "4096MB", "4GB", "4 GB", "4096 mb"
    $compact = str_replace(' ', '', $up);
    if (preg_match('/^(\d+)(MB|GB)$/', $compact, $m)) {
        $n = (int) $m[1];
        $u = $m[2];
        return $n > 0 ? [$n, $u, null] : [null, null, null];
    }

    // Si trae algo como "4gb aprox" o "4096mb (shared)" -> extrae primer número + unidad si existe
    if (preg_match('/(\d+)\s*(MB|GB)/i', (string)$raw, $m)) {
        $n = (int) $m[1];
        $u = strtoupper($m[2]);
        return $n > 0 ? [$n, $u, null] : [null, null, null];
    }

    // No es interpretable -> lo mandamos como nota (no rompe el INT)
    return [null, null, 'VRAM legacy no numérica: ' . (string)$raw];
};


        Equipo::query()
            ->select([
                'id',
                'tipo_equipo',
                'grafica_integrada_modelo',
                'grafica_dedicada_modelo',
                'grafica_dedicada_vram',
                'tiene_tarjeta_dedicada',
            ])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$total, &$created, &$updated, &$deleted, $dry, $only, $normText, $inferMarca, $parseVram) {

                DB::transaction(function () use ($rows, &$total, &$created, &$updated, &$deleted, $dry, $only, $normText, $inferMarca, $parseVram) {

                    foreach ($rows as $e) {
                        $total++;

                        $tipoEq = $e->tipo_equipo;
                        $isLaptopLike = in_array($tipoEq, ['LAPTOP','2 EN 1','ALL IN ONE','TABLET'], true);

                        // ================= INTEGRADA =================
                        if ($only === '' || $only === 'INTEGRADA') {
                            $modelo = $normText($e->grafica_integrada_modelo);
                            $debeExistir = $isLaptopLike || !empty($modelo);

                            if ($debeExistir) {
                                $payload = [
                                    'activo' => 1,
                                    'marca' => $inferMarca($modelo),
                                    'modelo' => $modelo,
                                    'vram' => null,
                                    'vram_unidad' => null,
                                    'notas' => null,
                                ];

                                if (!$dry) {
                                    $row = EquipoGpu::updateOrCreate(
                                        ['equipo_id' => $e->id, 'tipo' => 'INTEGRADA'],
                                        $payload
                                    );
                                    $row->wasRecentlyCreated ? $created++ : $updated++;
                                }
                            } else {
                                if (!$dry) {
                                    $deleted += EquipoGpu::where('equipo_id', $e->id)
                                        ->where('tipo', 'INTEGRADA')
                                        ->delete();
                                }
                            }
                        }

                        // ================= DEDICADA =================
                        if ($only === '' || $only === 'DEDICADA') {
                            $modeloD = $normText($e->grafica_dedicada_modelo);
                            [$vram, $unidad, $notaVram] = $parseVram($e->grafica_dedicada_vram);

                            $flag = (bool) $e->tiene_tarjeta_dedicada;
                            $debeExistir = $flag || !empty($modeloD) || !empty($vram);

                            if ($debeExistir) {
                                $payload = [
                                    'activo' => 1,
                                    'marca' => $inferMarca($modeloD),
                                    'modelo' => $modeloD,
                                    'vram' => $vram, // siempre INT o NULL
                                    'vram_unidad' => $vram ? ($unidad ?: 'GB') : null,
                                    'notas' => $notaVram, // guarda lo raro aquí
                                ];

                                if (!$dry) {
                                    $row = EquipoGpu::updateOrCreate(
                                        ['equipo_id' => $e->id, 'tipo' => 'DEDICADA'],
                                        $payload
                                    );
                                    $row->wasRecentlyCreated ? $created++ : $updated++;
                                }
                            } else {
                                if (!$dry) {
                                    $deleted += EquipoGpu::where('equipo_id', $e->id)
                                        ->where('tipo', 'DEDICADA')
                                        ->delete();
                                }
                            }
                        }
                    }
                });
            });

        $this->info("Equipos procesados: {$total}");
        $this->info("Creados: {$created} | Actualizados: {$updated} | Eliminados: {$deleted}");

        $this->info('Listo.');
        return self::SUCCESS;
    }
}
