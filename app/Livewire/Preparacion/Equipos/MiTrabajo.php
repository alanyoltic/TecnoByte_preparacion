<?php

namespace App\Livewire\Preparacion\Equipos;

use Livewire\Component;
use Livewire\Attributes\Layout;  // ← agregar este use
use Livewire\Attributes\Title; 
use App\Models\Asignacion;
use App\Models\AsignacionEquipo;
use App\Models\Equipo;
use App\Models\SolicitudPieza;
use App\Models\CatalogoPieza;
use Illuminate\Support\Facades\Auth;



#[Layout('layouts.app', ['pageTitle' => 'Mi Trabajo'])]

class MiTrabajo extends Component
{
    // ── Vista activa ──────────────────────────────────────────────────────
    // 'lista' | 'equipos' | 'trabajar'
    public string $vista = 'lista';

    // ── Asignación seleccionada ───────────────────────────────────────────
    public ?int $asignacionId = null;
    public ?int $asignacionEquipoId = null;

    // ── Formulario escaneo de serie ───────────────────────────────────────
    public string $numeroSerie = '';

    // ── Formulario terminar equipo ────────────────────────────────────────
    public string $camino = 'COMPLETADO'; // COMPLETADO | PIEZA_PENDIENTE | GARANTIA_INTERNA | GARANTIA_EXTERNA
    public string $notas = '';

    // ── Formulario pieza faltante ─────────────────────────────────────────
    public ?int $catalogoPiezaId = null;
    public string $descripcionPiezaLibre = '';

    // ── Errores manuales ──────────────────────────────────────────────────
    public string $error = '';

    protected function rules(): array
    {
        return [
            'numeroSerie' => 'required|string|min:3',
        ];
    }

    // ── Computed: asignaciones activas del técnico ────────────────────────
    public function getAsignacionesProperty()
    {
        return Asignacion::with(['loteModelo.lote', 'equipos'])
            ->delTecnico(Auth::id())
            ->whereIn('estatus', [Asignacion::PENDIENTE, Asignacion::EN_PROCESO])
            ->orderByDesc('fecha_asignacion')
            ->get();
    }

    // ── Computed: asignación seleccionada ─────────────────────────────────
    public function getAsignacionActualProperty(): ?Asignacion
    {
        if (!$this->asignacionId) return null;
        return Asignacion::with(['loteModelo.lote', 'equipos.equipo'])->find($this->asignacionId);
    }

    // ── Computed: equipo en trabajo ───────────────────────────────────────
    public function getEquipoEnTrabajoProperty(): ?AsignacionEquipo
    {
        if (!$this->asignacionEquipoId) return null;
        return AsignacionEquipo::with('equipo')->find($this->asignacionEquipoId);
    }

    // ── Navegar a detalle de asignación ───────────────────────────────────
    public function verEquipos(int $asignacionId): void
    {
        $this->asignacionId = $asignacionId;
        $this->vista = 'equipos';
        $this->resetErrorBag();
        $this->error = '';
    }

    // ── Volver a lista ────────────────────────────────────────────────────
    public function volverALista(): void
    {
        $this->vista = 'lista';
        $this->asignacionId = null;
        $this->asignacionEquipoId = null;
        $this->reset(['numeroSerie', 'camino', 'notas', 'error']);
    }

    public function volverAEquipos(): void
    {
        $this->vista = 'equipos';
        $this->asignacionEquipoId = null;
        $this->reset(['camino', 'notas', 'error',
                      'catalogoPiezaId', 'descripcionPiezaLibre']);
    }

    // ── Escanear número de serie ──────────────────────────────────────────
    public function escanearSerie(): void
    {
        $this->error = '';
        $this->validate(['numeroSerie' => 'required|string|min:3']);

        // Buscar equipo por número de serie
        $equipo = Equipo::where('numero_serie', trim($this->numeroSerie))->first();

        if (!$equipo) {
            $this->error = 'No se encontró ningún equipo con ese número de serie.';
            return;
        }

        // Verificar que el equipo pertenece a la asignación actual
        $asignacion = $this->asignacionActual;
        if ($equipo->lote_modelo_id !== $asignacion->lote_modelo_id) {
            $this->error = 'Este equipo no corresponde al modelo asignado.';
            return;
        }

        // Verificar que no esté ya en otra asignación activa
        $yaAsignado = AsignacionEquipo::where('equipo_id', $equipo->id)
            ->where('camino', AsignacionEquipo::EN_PROCESO)
            ->exists();

        if ($yaAsignado) {
            $this->error = 'Este equipo ya está siendo trabajado por otro técnico.';
            return;
        }

        // Crear registro en asignacion_equipos
        $ae = AsignacionEquipo::create([
            'asignacion_id' => $this->asignacionId,
            'equipo_id'     => $equipo->id,
            'inicio_en'     => now(),
            'camino'        => AsignacionEquipo::EN_PROCESO,
        ]);

        // Actualizar estatus de la asignación
        $asignacion->update(['estatus' => Asignacion::EN_PROCESO]);

        $this->asignacionEquipoId = $ae->id;
        $this->camino = 'COMPLETADO';
        $this->notas = '';
        $this->vista = 'trabajar';
        $this->numeroSerie = '';

        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => 'Equipo iniciado: ' . $equipo->numero_serie,
        ]);
    }

    // ── Continuar trabajando en equipo ya iniciado ────────────────────────
    public function continuarEquipo(int $asignacionEquipoId): void
    {
        $this->asignacionEquipoId = $asignacionEquipoId;
        $ae = AsignacionEquipo::find($asignacionEquipoId);
        $this->camino = $ae->camino;
        $this->notas  = $ae->notas ?? '';
        $this->vista  = 'trabajar';
    }

    // ── Terminar equipo ───────────────────────────────────────────────────
    public function terminarEquipo(): void
    {
        $this->error = '';

        // Validar pieza si el camino es PIEZA_PENDIENTE
        if ($this->camino === 'PIEZA_PENDIENTE') {
            if (!$this->catalogoPiezaId && empty(trim($this->descripcionPiezaLibre))) {
                $this->error = 'Debes especificar qué pieza falta.';
                return;
            }
        }

        $ae = AsignacionEquipo::with(['asignacion', 'equipo'])->find($this->asignacionEquipoId);

        // Actualizar asignacion_equipos
        $ae->update([
            'fin_en' => now(),
            'camino' => $this->camino,
            'notas'  => $this->notas,
        ]);

        // Actualizar estatus del equipo según camino
        $estatusCiclo = match($this->camino) {
            'COMPLETADO'       => 'CALIDAD',
            'PIEZA_PENDIENTE'  => 'CEDIS',
            'GARANTIA_INTERNA' => 'CEDIS',
            'GARANTIA_EXTERNA' => 'CEDIS',
            default            => 'CEDIS',
        };

        $estatusArea = match($this->camino) {
            'COMPLETADO'       => 'LISTO',
            'PIEZA_PENDIENTE'  => 'PENDIENTE_PIEZA',
            'GARANTIA_INTERNA' => 'PENDIENTE_GARANTIA',
            'GARANTIA_EXTERNA' => 'PENDIENTE_GARANTIA',
            default            => 'EN_PROCESO',
        };

        $ae->equipo->update([
            'estatus_ciclo' => $estatusCiclo,
            'estatus_area'  => $estatusArea,
        ]);

        // Si hay pieza faltante, crear solicitud y registro
        if ($this->camino === 'PIEZA_PENDIENTE') {
            SolicitudPieza::create([
                'asignacion_equipo_id' => $ae->id,
                'solicitado_por_id'    => Auth::id(),
                'catalogo_pieza_id'    => $this->catalogoPiezaId ?: null,
                'descripcion_libre'    => $this->descripcionPiezaLibre ?: null,
                'estatus'              => 'PENDIENTE',
            ]);
        }

        // Registrar puntos
        $equipo = $ae->equipo;
        $clasificacionId = $equipo->clasificacion_puntos_id;
        $puntosBase = $clasificacionId
            ? \App\Models\ClasificacionPuntos::find($clasificacionId)?->puntos_base ?? 1.0
            : 1.0;

        $rol = match($this->camino) {
            'COMPLETADO'       => \App\Models\PuntoTecnico::COMPLETO,
            'PIEZA_PENDIENTE'  => \App\Models\PuntoTecnico::INICIO_PIEZA,
            'GARANTIA_INTERNA',
            'GARANTIA_EXTERNA' => \App\Models\PuntoTecnico::GARANTIA,
            default            => \App\Models\PuntoTecnico::COMPLETO,
        };

        \App\Models\PuntoTecnico::registrar(
            tecnicoId:           Auth::id(),
            asignacionEquipoId:  $ae->id,
            rol:                 $rol,
            puntosBase:          (float) $puntosBase,
            clasificacionId:     $clasificacionId,
        );

        // Verificar si la asignación está completa
        $asignacion = $ae->asignacion;
        $terminados = AsignacionEquipo::where('asignacion_id', $asignacion->id)
            ->where('camino', '!=', AsignacionEquipo::EN_PROCESO)
            ->count();

        if ($terminados >= $asignacion->cantidad) {
            $asignacion->update([
                'estatus'        => Asignacion::ENTREGADO,
                'fecha_entrega'  => now()->toDateString(),
            ]);
        }

        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => 'Equipo terminado correctamente.',
        ]);

        $this->volverAEquipos();
    }

    public function render()
    {
        return view('livewire.preparacion.equipos.mi-trabajo', [
            'catalogoPiezas' => CatalogoPieza::activos()->orderBy('categoria')->orderBy('nombre')->get(),
        ]);
    }
}
