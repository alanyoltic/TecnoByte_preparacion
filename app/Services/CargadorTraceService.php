<?php

namespace App\Services;

use App\Models\Cargador;
use App\Models\CargadorAuditoria;
use Illuminate\Support\Facades\Auth;

class CargadorTraceService
{
    /**
     * Registra un movimiento/acción en la auditoría de un cargador.
     *
     * @param Cargador $cargador
     * @param string $accion
     * @param string|null $detalles
     * @return CargadorAuditoria
     */
    public static function log(Cargador $cargador, string $accion, ?string $detalles = null): CargadorAuditoria
    {
        return CargadorAuditoria::create([
            'cargador_id' => $cargador->id,
            'user_id' => Auth::id(),
            'accion' => $accion,
            'detalles' => $detalles,
        ]);
    }
}
