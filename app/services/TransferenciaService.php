<?php

namespace App\Services;

use App\Models\AlmacenEncargado;
use App\Models\Transferencia;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TransferenciaService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    protected function esEncargadoActivo($almacenId)
    {
        return AlmacenEncargado::where('almacen_id', $almacenId)
            ->where('user_id', $this->user->id)
            ->where('activo', 1)
            ->where(function ($q) {
                $q->whereNull('hasta')
                    ->orWhere('hasta', '>=', Carbon::now());
            })
            ->where('desde', '<=', Carbon::now())
            ->exists();
    }

    public function enviar(Transferencia $transferencia)
    {
        if ($transferencia->estatus !== 'BORRADOR') {
            throw new Exception('La transferencia no está en estado BORRADOR.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_origen_id)) {
            throw new Exception('No eres encargado del almacén origen.');
        }

        if ($transferencia->detalles()->count() === 0) {
            throw new Exception('No puedes enviar una transferencia sin productos.');
        }

        $transferencia->update([
            'estatus' => 'ENVIADA',
            'enviada_at' => now(),
        ]);
    }

    public function aceptar(Transferencia $transferencia)
    {
        if ($transferencia->estatus !== 'ENVIADA') {
            throw new Exception('La transferencia no está en estado ENVIADA.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No eres encargado del almacén destino.');
        }

        $transferencia->update([
            'estatus' => 'ACEPTADA',
            'approved_by' => $this->user->id,
            'aprobada_at' => now(),
        ]);

        // 🔥 ETAPA 3 aplicará movimientos reales aquí
    }

    public function rechazar(Transferencia $transferencia, $motivo = null)
    {
        if ($transferencia->estatus !== 'ENVIADA') {
            throw new Exception('Solo se pueden rechazar transferencias ENVIADAS.');
        }

        if (! $this->esEncargadoActivo($transferencia->almacen_destino_id)) {
            throw new Exception('No eres encargado del almacén destino.');
        }

        $transferencia->update([
            'estatus' => 'RECHAZADA',
            'approved_by' => $this->user->id,
            'aprobada_at' => now(),
            'observaciones' => $motivo,
        ]);
    }
}
