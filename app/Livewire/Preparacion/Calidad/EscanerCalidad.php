<?php

namespace App\Livewire\Preparacion\Calidad;

use App\Models\Equipo;
use App\Models\ValidacionCalidad;
use App\Services\CalidadService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['pageTitle' => 'Escáner Automático de Calidad'])]
class EscanerCalidad extends Component
{
    // Modos: 'ESCANEAR', 'CONFIRMAR', 'ELEGIR_ACCION', 'FORMULARIO_APROBAR', 'FORMULARIO_RECHAZAR'
    public string $modo = 'ESCANEAR';

    public string $numeroSerie = '';
    public ?Equipo $equipo = null;
    public string $error = '';

    // Formulario de aprobación
    public ?int $calificacion = null;
    public array $qSalioBien = [];
    public string $notasValidacion = '';

    // Formulario de rechazo
    public string $motivoRechazo = '';
    public array $qSalioMal = [];
    public array $qSalioBienRechazo = [];
    public ?int $calificacionRechazo = null;
    public string $notasRechazo = '';

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->tienePermiso('prep.calidad.validar'),
            403
        );
    }

    /**
     * Búsqueda por número de serie
     */
    public function buscarEquipo($serie = null): void
    {
        $this->error = '';
        if ($serie !== null) {
            $this->numeroSerie = trim((string) $serie);
        } else {
            $this->numeroSerie = trim($this->numeroSerie);
        }

        if (empty($this->numeroSerie)) {
            $this->dispatch('toast', type: 'error', message: 'El código está vacío.');
            return;
        }

        // Buscar por numero_serie o por ID interno
        $equipoEncontrado = Equipo::where('numero_serie', $this->numeroSerie)
                                  ->orWhere('id', $this->numeroSerie)
                                  ->first();

        if (!$equipoEncontrado) {
            $msg = "No se encontró ningún equipo con el código o ID: {$this->numeroSerie}";
            $this->error = $msg;
            $this->dispatch('toast', type: 'error', message: $msg);
            $this->dispatch('focus-scanner');
            return;
        }

        if ($equipoEncontrado->estatus_area !== Equipo::AREA_EN_CALIDAD) {
            $msg = "El equipo {$equipoEncontrado->numero_serie} (ID: {$equipoEncontrado->id}) NO está en calidad (Estado actual: {$equipoEncontrado->estatus_area}).";
            $this->error = $msg;
            $this->dispatch('toast', type: 'error', message: $msg);
            $this->dispatch('focus-scanner');
            return;
        }

        // Cargar relaciones para mostrar
        $equipoEncontrado->load(['loteModelo.catalogoEquipo', 'asignacionEquipos' => function ($q) {
            $q->where('camino', \App\Models\AsignacionEquipo::EN_CALIDAD)->latest('fin_en');
        }, 'asignacionEquipos.tecnico']);

        $this->equipo = $equipoEncontrado;
        $this->modo = 'CONFIRMAR';
    }

    public function confirmar(bool $esCorrecto): void
    {
        if ($esCorrecto) {
            $this->modo = 'ELEGIR_ACCION';
        } else {
            $this->resetEscaneo();
        }
    }

    public function seleccionarAccion(string $accion): void
    {
        if ($accion === 'aprobar') {
            $this->modo = 'FORMULARIO_APROBAR';
            // Valores por defecto para una aprobación rápida: 5 estrellas y todo bien
            $this->calificacion = 5;
            $this->qSalioBien = [
                'Limpieza interior y exterior impecable',
                'Instalación de SO y drivers sin errores',
                'Pruebas de estrés (CPU, RAM, Discos) superadas',
                'Componentes (batería, teclado, pantalla) 100% funcionales',
                'Pasta térmica y pads aplicados correctamente',
                'Etiquetas y estética general en orden'
            ];
            $this->notasValidacion = '';
        } elseif ($accion === 'rechazar') {
            $this->modo = 'FORMULARIO_RECHAZAR';
            $this->motivoRechazo = '';
            $this->qSalioMal = [];
            $this->qSalioBienRechazo = [];
            $this->calificacionRechazo = null;
            $this->notasRechazo = '';
        }
    }

    public function validarEquipo(): void
    {
        $this->error = '';

        try {
            $service = new CalidadService;

            $service->validarEquipo(
                equipoId: $this->equipo->id,
                calificacion: $this->calificacion,
                qsalioBien: array_filter($this->qSalioBien),
                notas: trim($this->notasValidacion) ?: null
            );

            $this->dispatch('toast',
                type: 'success',
                title: 'Equipo validado',
                message: "Equipo {$this->equipo->numero_serie} aprobado exitosamente."
            );

            $this->resetEscaneo();
        } catch (\Throwable $e) {
            $this->error = "Error al validar: {$e->getMessage()}";
        }
    }

    public function rechazarEquipo(): void
    {
        $this->error = '';

        if (empty(trim($this->motivoRechazo))) {
            $this->error = 'El motivo del rechazo es obligatorio.';
            return;
        }

        try {
            $service = new CalidadService;

            $service->rechazarEquipo(
                equipoId: $this->equipo->id,
                motivo: trim($this->motivoRechazo),
                qSalioMal: array_filter($this->qSalioMal),
                qSalioBien: array_filter($this->qSalioBienRechazo),
                calificacion: $this->calificacionRechazo,
                notas: trim($this->notasRechazo) ?: null
            );

            $this->dispatch('toast',
                type: 'warning',
                title: 'Equipo rechazado',
                message: "Equipo {$this->equipo->numero_serie} regresado a preparación."
            );

            $this->resetEscaneo();
        } catch (\Throwable $e) {
            $this->error = "Error al rechazar: {$e->getMessage()}";
        }
    }

    public function resetEscaneo(): void
    {
        $this->modo = 'ESCANEAR';
        $this->numeroSerie = '';
        $this->equipo = null;
        $this->error = '';
        
        $this->calificacion = null;
        $this->qSalioBien = [];
        $this->notasValidacion = '';
        
        $this->motivoRechazo = '';
        $this->qSalioMal = [];
        $this->qSalioBienRechazo = [];
        $this->calificacionRechazo = null;
        $this->notasRechazo = '';

        $this->dispatch('focus-scanner');
    }

    public function render()
    {
        return view('livewire.preparacion.calidad.escaner-calidad');
    }
}
