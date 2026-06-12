<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->string('categoria_solicitada', 120)
                ->nullable()
                ->after('descripcion_libre');
            $table->string('detalle_solicitado', 255)
                ->nullable()
                ->after('categoria_solicitada');
            $table->index('categoria_solicitada', 'solicitudes_piezas_categoria_solicitada_idx');
        });

        $parseDescripcion = static function (?string $descripcion): array {
            $descripcion = trim((string) $descripcion);

            if ($descripcion === '') {
                return [null, null];
            }

            if (preg_match('/^\[(.+?)\]\s*(.*)$/u', $descripcion, $matches) === 1) {
                $categoria = trim((string) ($matches[1] ?? '')) ?: null;
                $detalle = trim((string) ($matches[2] ?? '')) ?: null;

                return [$categoria, $detalle];
            }

            $partes = preg_split('/\s+[—–-]\s+/u', $descripcion, 2);

            if (is_array($partes) && count($partes) === 2) {
                $categoria = trim((string) ($partes[0] ?? '')) ?: null;
                $detalle = trim((string) ($partes[1] ?? '')) ?: null;

                return [$categoria, $detalle];
            }

            return [$descripcion, null];
        };

        DB::table('solicitudes_piezas')
            ->select(['id', 'descripcion_libre', 'categoria_solicitada', 'detalle_solicitado'])
            ->orderBy('id')
            ->chunkById(100, function ($solicitudes) use ($parseDescripcion) {
                foreach ($solicitudes as $solicitud) {
                    if ($solicitud->categoria_solicitada !== null || $solicitud->detalle_solicitado !== null) {
                        continue;
                    }

                    [$categoria, $detalle] = $parseDescripcion($solicitud->descripcion_libre);

                    if ($categoria === null && $detalle === null) {
                        continue;
                    }

                    DB::table('solicitudes_piezas')
                        ->where('id', $solicitud->id)
                        ->update([
                            'categoria_solicitada' => $categoria,
                            'detalle_solicitado' => $detalle,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('solicitudes_piezas', function (Blueprint $table) {
            $table->dropIndex('solicitudes_piezas_categoria_solicitada_idx');
            $table->dropColumn(['categoria_solicitada', 'detalle_solicitado']);
        });
    }
};
