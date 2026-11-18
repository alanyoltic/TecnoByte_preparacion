<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Equipo;
use App\Models\EquipoEstancia;
use App\Models\EquipoMovimiento;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipoMovimientoService
{
    private const TIPOS_VALIDOS = [
        'ALTA_LOTE',
        'ALTA_MANUAL',
        'MOVER_ALMACEN',
        'ASIGNAR_TECNICO',
        'FINALIZAR_TECNICO',
        'VENTA',
        'BAJA',
        'AJUSTE',
    ];

    private function normalizarTipoMovimiento(string $tipo, ?string &$motivo = null): string
    {
        $tipoNormalizado = strtoupper(trim($tipo));

        if (in_array($tipoNormalizado, self::TIPOS_VALIDOS, true)) {
            return $tipoNormalizado;
        }

        // Evita SQLSTATE[01000] (truncamiento por ENUM inválido) y deja rastro.
        $motivoOriginal = $motivo;
        $motivo = trim(implode(' | ', array_filter([
            $motivoOriginal,
            "Tipo movimiento inválido: {$tipoNormalizado}",
        ])));

        logger()->warning('Tipo de movimiento inválido normalizado a AJUSTE', [
            'tipo_original' => $tipo,
            'tipo_normalizado' => $tipoNormalizado,
        ]);

        return 'AJUSTE';
    }

    /**
     * Mueve un equipo a otro almacén.
     */
    public function mover(
        Equipo $equipo,
        Almacen $destino,
        string $tipo = 'MOVER_ALMACEN',
        ?string $motivo = null
    ): void {

        DB::transaction(function () use ($equipo, $destino, $tipo, $motivo) {
            $tipo = $this->normalizarTipoMovimiento($tipo, $motivo);

            $usuario = auth()->user();

            if (! $usuario) {
                throw new \Exception('Usuario no autenticado.');
            }

            // 🔒 Bloquear equipo
            $equipo = Equipo::where('id', $equipo->id)
                ->lockForUpdate()
                ->first();

            $almacenActualId = $equipo->almacen_id;

            if ($almacenActualId == $destino->id) {
                // Si ya está registrado en ese almacén, asegurar que tenga estancia abierta
                $estancia = EquipoEstancia::where('equipo_id', $equipo->id)
                    ->whereNull('fin_at')
                    ->first();
                if (! $estancia) {
                    EquipoEstancia::create([
                        'equipo_id' => $equipo->id,
                        'almacen_id' => $destino->id,
                        'inicio_at' => now(),
                        'abierto_por' => $usuario->id,
                    ]);
                }
                return;
            }

            // 🔎 Obtener estancia abierta
            $estanciaActual = EquipoEstancia::where('equipo_id', $equipo->id)
                ->whereNull('fin_at')
                ->lockForUpdate()
                ->first();

            if (! $estanciaActual) {
                $estanciaActual = EquipoEstancia::create([
                    'equipo_id' => $equipo->id,
                    'almacen_id' => $almacenActualId ?: Almacen::PREPARACION,
                    'inicio_at' => $equipo->created_at ?? now(),
                    'abierto_por' => $usuario->id,
                ]);
            }

            $ahora = now();

            // Cerrar estancia actual
            $estanciaActual->update([
                'fin_at' => $ahora,
                'cerrado_por' => $usuario->id,
            ]);

            // Abrir nueva estancia
            EquipoEstancia::create([
                'equipo_id' => $equipo->id,
                'almacen_id' => $destino->id,
                'inicio_at' => $ahora,
                'abierto_por' => $usuario->id,
            ]);

            // Registrar movimiento
            EquipoMovimiento::create([
                'equipo_id' => $equipo->id,
                'tipo' => $tipo,
                'desde_almacen_id' => $almacenActualId,
                'hacia_almacen_id' => $destino->id,
                'motivo' => $motivo,
                'created_by' => $usuario->id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Actualizar equipo
            $equipo->update([
                'almacen_id' => $destino->id,
            ]);
        });
    }

    /**
     * Abre la primera estancia de un equipo (por ejemplo al darlo de alta).
     */
    public function abrirEstanciaInicial(
        Equipo $equipo,
        Almacen $almacen,
        string $tipo = 'ALTA_LOTE',
        ?string $motivo = null
    ): void {

        DB::transaction(function () use ($equipo, $almacen, $tipo, $motivo) {
            $tipo = $this->normalizarTipoMovimiento($tipo, $motivo);

            $usuario = Auth::user();

            if (! $usuario) {
                throw new Exception('Usuario no autenticado.');
            }

            // 🔄 Recargar equipo desde BD con lock
            $equipo = Equipo::where('id', $equipo->id)
                ->lockForUpdate()
                ->first();

            // 🔎 Validar estancia abierta DENTRO de la transacción
            $estanciaAbierta = EquipoEstancia::where('equipo_id', $equipo->id)
                ->whereNull('fin_at')
                ->lockForUpdate()
                ->exists();

            if ($estanciaAbierta) {
                throw new Exception('El equipo ya tiene una estancia abierta.');
            }

            $ahora = now();

            EquipoEstancia::create([
                'equipo_id' => $equipo->id,
                'almacen_id' => $almacen->id,
                'inicio_at' => $ahora,
                'abierto_por' => $usuario->id,
            ]);

            EquipoMovimiento::create([
                'equipo_id' => $equipo->id,
                'tipo' => $tipo,
                'desde_almacen_id' => null,
                'hacia_almacen_id' => $almacen->id,
                'motivo' => $motivo,
                'created_by' => $usuario->id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $equipo->update([
                'almacen_id' => $almacen->id,
            ]);
        });
    }

    /**
     * Registra un movimiento directo de equipo (p. ej. VENTA o ajuste) y cierra la estancia si es salida.
     */
    public function registrar(
        Equipo $equipo,
        string $tipo,
        ?int $desde = null,
        ?int $hacia = null,
        ?string $motivo = null,
        ?int $createdBy = null
    ): EquipoMovimiento {
        return DB::transaction(function () use ($equipo, $tipo, $desde, $hacia, $motivo, $createdBy) {
            $tipo = $this->normalizarTipoMovimiento($tipo, $motivo);
            $userId = $createdBy ?? Auth::id() ?? 1;

            // Si es salida o venta, cerrar la estancia abierta actual
            if ($tipo === 'VENTA' || $hacia === null) {
                $estanciaActual = EquipoEstancia::where('equipo_id', $equipo->id)
                    ->whereNull('fin_at')
                    ->first();

                if ($estanciaActual) {
                    $estanciaActual->update([
                        'fin_at' => now(),
                        'cerrado_por' => $userId,
                    ]);
                }
            }

            return EquipoMovimiento::create([
                'equipo_id' => $equipo->id,
                'tipo' => $tipo,
                'desde_almacen_id' => $desde,
                'hacia_almacen_id' => $hacia,
                'motivo' => $motivo,
                'created_by' => $userId,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
