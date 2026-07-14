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
                throw new \Exception('El equipo ya está en ese almacén.');
            }

            // 🔎 Obtener estancia abierta
            $estanciaActual = EquipoEstancia::where('equipo_id', $equipo->id)
                ->whereNull('fin_at')
                ->lockForUpdate()
                ->first();

            if (! $estanciaActual) {
                throw new \Exception('No existe estancia abierta.');
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
}
