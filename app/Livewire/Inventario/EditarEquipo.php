<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Livewire\Forms\EquipoForm;
use Illuminate\Support\Facades\Schema;
use App\Models\{Equipo, Lote, Proveedor, LoteModeloRecibido, EquipoBateria, EquipoMonitor, EquipoAuditoria};
use Illuminate\Support\Facades\{Auth, DB};

class EditarEquipo extends Component
{
    public Equipo $equipo;
    public EquipoForm $form; 



    public array $baseline = [];
    public bool $hasChanges = false;


    // Catalogos
    public array $lotes = [];
    public array $proveedores = [];
    public array $modelosLote = [];
    
    // UI Dinámica
    public array $puertos_usb = [];
    public array $puertos_video = [];
    public array $slots_almacenamiento = [];

    // Baterías
    public $bateria_tiene = true;   
    public $bateria2_tiene = false;
 

    


    // Detalles Estéticos y Funcionamiento
    public array $detalles_esteticos_checks = []; 
    public $detalles_esteticos_otro;            
    public array $detalles_funcionamiento_checks = []; 
    public $detalles_funcionamiento_otro;

    public array $originalSnapshot = [];
    public ?string $origen_pantalla = null;


    private const MAP_USB = ['USB 2.0' => 'puertos_usb_2', 'USB 3.0' => 'puertos_usb_30', 'USB 3.1' => 'puertos_usb_31', 'USB 3.2' => 'puertos_usb_32', 'USB-C' => 'puertos_usb_c'];
    private const MAP_VIDEO = ['HDMI' => 'puertos_hdmi', 'Mini HDMI' => 'puertos_mini_hdmi', 'VGA' => 'puertos_vga', 'DVI' => 'puertos_dvi', 'DisplayPort' => 'puertos_displayport', 'Mini DisplayPort' => 'puertos_mini_dp'];
    private const MAP_SLOTS = ['SSD' => 'slots_alm_ssd', 'M.2' => 'slots_alm_m2', 'M.2 MICRO' => 'slots_alm_m2_micro', 'HDD' => 'slots_alm_hdd', 'MSATA' => 'slots_alm_msata'];
    private const MAP_MONITOR_IN = [
    'HDMI'             => 'in_hdmi',
    'Mini HDMI'        => 'in_mini_hdmi',
    'VGA'              => 'in_vga',
    'DVI'              => 'in_dvi',
    'DisplayPort'      => 'in_displayport',
    'Mini DisplayPort' => 'in_mini_displayport',
    'USB 2.0'          => 'in_usb_2',
    'USB 3.0'          => 'in_usb_3',
    'USB 3.1'          => 'in_usb_31',
    'USB 3.2'          => 'in_usb_32',
    'USB-C'            => 'in_usb_c',
];



    private function guardarBaterias(): void
{
    // Si el usuario indica que NO tiene batería, eliminamos cualquier registro
    if (!($this->form->bateria_tiene ?? false)) {
        EquipoBateria::where('equipo_id', $this->equipo->id)->delete();
        return;
    }

    // Armar filas a guardar (1 o 2)
    $rows = [];

    // Batería 1
    if (!empty($this->form->bateria1_tipo)) {
        $rows[] = [
            'tipo' => $this->form->bateria1_tipo,
            'salud_percent' => $this->form->bateria1_salud !== null && $this->form->bateria1_salud !== ''
                ? (int)$this->form->bateria1_salud
                : null,
        ];
    }

    // Batería 2 (solo si está activada)
    if (($this->form->bateria2_tiene ?? false) && !empty($this->form->bateria2_tipo)) {
        $rows[] = [
            'tipo' => $this->form->bateria2_tipo,
            'salud_percent' => $this->form->bateria2_salud !== null && $this->form->bateria2_salud !== ''
                ? (int)$this->form->bateria2_salud
                : null,
        ];
    }

    // Reemplazo total (estable y sin edge-cases)
    EquipoBateria::where('equipo_id', $this->equipo->id)->delete();

    foreach ($rows as $r) {
        EquipoBateria::create([
            'equipo_id' => $this->equipo->id,
            'tipo' => $r['tipo'],
            'salud_percent' => $r['salud_percent'],
        ]);
    }
}


public function updatedFormRamEsSoldada($value): void
{
    $value = (bool) $value;

    if (!$value) {
        // Si ya no es soldada, limpiamos todo lo relacionado
        $this->form->ram_cantidad_soldada = null;
        $this->form->ram_sin_slots = false;

        // Si tienes estos campos en tu tabla:
        $this->form->ram_slots_totales = $this->form->ram_slots_totales; // no tocar
        $this->form->ram_expansion_max = $this->form->ram_expansion_max; // no tocar

        
    }
}



    


public function mount(Equipo $equipo)
{
    $this->equipo = $equipo;

    $this->form->setEquipo($equipo);   // <- aquí ya se llena el form
    $this->cargarCatalogos();

    $this->hidratarUI();

    $this->detalles_esteticos_checks = array_filter(explode(', ', $equipo->detalles_esteticos ?? ''));
    $this->detalles_funcionamiento_checks = array_filter(explode(', ', $equipo->detalles_funcionamiento ?? ''));

    $this->originalSnapshot = $this->form->all();

    $this->baseline = $this->form->snapshotPersistible();
    $this->hasChanges = false;
}



    public function recalcChanges(): void
{
    $current = $this->form->snapshotPersistible();
    $this->hasChanges = $current !== $this->baseline;
}


    private function hidratarUI()
{
    // Reset UI arrays (evita duplicados)
    $this->puertos_usb = [];
    $this->puertos_video = [];
    $this->slots_almacenamiento = [];

    // 1) Puertos y slots desde columnas de equipos (en el FORM)
    foreach (self::MAP_USB as $label => $col) {
        $qty = (int)($this->form->$col ?? 0);
        if ($qty > 0) $this->puertos_usb[] = ['tipo' => $label, 'cantidad' => $qty];
    }

    foreach (self::MAP_VIDEO as $label => $col) {
        $qty = (int)($this->form->$col ?? 0);
        if ($qty > 0) $this->puertos_video[] = ['tipo' => $label, 'cantidad' => $qty];
    }

    foreach (self::MAP_SLOTS as $label => $col) {
        $qty = (int)($this->form->$col ?? 0);
        if ($qty > 0) $this->slots_almacenamiento[] = ['tipo' => $label, 'cantidad' => $qty];
    }

    // 2) Monitor (tabla relacionada)
    $m = EquipoMonitor::where('equipo_id', $this->equipo->id)->first();

    if ($m) {
        $this->origen_pantalla = $m->origen_pantalla;

        if ($m->origen_pantalla === 'INTEGRADA') {
            $this->form->monitor_incluido = 'NO';

            $this->form->pantalla_pulgadas   = $m->pulgadas;
            $this->form->pantalla_resolucion = $m->resolucion;
            $this->form->pantalla_es_touch   = (bool)$m->es_touch;

            $this->form->monitor_pulgadas = null;
            $this->form->monitor_resolucion = null;
            $this->form->monitor_es_touch = false;
            $this->form->monitor_entradas_rows = [];

            // limpiar detalles monitor externo
            $this->form->monitor_detalles_esteticos_checks = '';
            $this->form->monitor_detalles_esteticos_otro = '';
            $this->form->monitor_detalles_funcionamiento_checks = '';
            $this->form->monitor_detalles_funcionamiento_otro = '';
        } else { // EXTERNA
            $this->form->monitor_incluido = ((int)($m->incluido ?? 1) === 1) ? 'SI' : 'NO';


            $this->form->monitor_pulgadas   = $m->pulgadas;
            $this->form->monitor_resolucion = $m->resolucion;
            $this->form->monitor_es_touch   = (bool)$m->es_touch;

            $this->form->pantalla_pulgadas = null;
            $this->form->pantalla_resolucion = null;
            $this->form->pantalla_es_touch = false;

            // detalles (strings)
            $this->form->monitor_detalles_esteticos_checks = (string)($m->detalles_esteticos_checks ?? '');
            $this->form->monitor_detalles_esteticos_otro  = (string)($m->detalles_esteticos_otro ?? '');

            $this->form->monitor_detalles_funcionamiento_checks = (string)($m->detalles_funcionamiento_checks ?? '');
            $this->form->monitor_detalles_funcionamiento_otro  = (string)($m->detalles_funcionamiento_otro ?? '');

            // Entradas in_* -> rows
            $rows = [];
            foreach (self::MAP_MONITOR_IN as $label => $col) {
                $qty = (int)($m->{$col} ?? 0);
                if ($qty > 0) $rows[] = ['tipo' => $label, 'cantidad' => $qty];
            }
            $this->form->monitor_entradas_rows = $rows;
        }
    } else {
        $this->origen_pantalla = null;

        $this->form->monitor_incluido = 'NO';
        $this->form->monitor_entradas_rows = [];

        $this->form->pantalla_pulgadas = null;
        $this->form->pantalla_resolucion = null;
        $this->form->pantalla_es_touch = false;

        $this->form->monitor_pulgadas = null;
        $this->form->monitor_resolucion = null;
        $this->form->monitor_es_touch = false;

        $this->form->monitor_detalles_esteticos_checks = '';
        $this->form->monitor_detalles_esteticos_otro = '';
        $this->form->monitor_detalles_funcionamiento_checks = '';
        $this->form->monitor_detalles_funcionamiento_otro = '';
    }

    // 3) Baterías (tabla relacionada) -> form
    $bats = EquipoBateria::query()
        ->where('equipo_id', $this->equipo->id)
        ->orderBy('id')
        ->get()
        ->values();

    $this->form->bateria_tiene = $bats->isNotEmpty();

    $this->form->bateria1_tipo  = $bats[0]->tipo ?? null;
    $this->form->bateria1_salud = isset($bats[0]) ? (string)((int)$bats[0]->salud_percent) : null;

    $this->form->bateria2_tiene = isset($bats[1]);
    $this->form->bateria2_tipo  = $bats[1]->tipo ?? null;
    $this->form->bateria2_salud = isset($bats[1]) ? (string)((int)$bats[1]->salud_percent) : null;
}

private function tipoKey(?string $v): string
{
    $v = mb_strtolower(trim((string) $v));
    $v = str_replace(['-', '_'], ' ', $v);
    $v = preg_replace('/\s+/', ' ', $v);
    return $v;
}

public function updated($name, $value): void
{
    $this->recalcChanges();
}


public function getPantallaIntegradaProperty(): bool
{
    // 1) Si ya existe registro en equipo_monitores, manda eso
    if (($this->origen_pantalla ?? null) === 'INTEGRADA') return true;
    if (($this->origen_pantalla ?? null) === 'EXTERNA') return false;

    // 2) Fallback por tipo_equipo (cuando aún no hay monitor guardado)
    $tipo = $this->tipoKey($this->form->tipo_equipo ?? null);

    return in_array($tipo, [
        'laptop',
        'all in one',
        'all in one pc',
        'all in one aio',
        'tablet',
        '2 en 1',
        '2 in 1',
        '2 en1',
    ], true);
}

public function getPantallaExternaProperty(): bool
{
    if (($this->origen_pantalla ?? null) === 'EXTERNA') return true;
    if (($this->origen_pantalla ?? null) === 'INTEGRADA') return false;

    $tipo = $this->tipoKey($this->form->tipo_equipo ?? null);

    return in_array($tipo, [
        'escritorio',
        'desktop',
        'micro pc',
        'micro pc gamer',
        'gamer',
        'cpu',
    ], true);
}

public function actualizar()
{


        $this->recalcChanges();

    if (!$this->hasChanges) {
        $this->dispatch('toast', type: 'info', message: 'No hay cambios por guardar.');
        return;
    }

    $this->form->validate();


    $this->form->validate();

    // 1) sincroniza UI arrays -> columnas del FORM
    $this->sincronizarUAlForm();

    // 2) Payload SOLO con columnas reales de la tabla equipos
    $all = $this->form->all();

    $columns = Schema::getColumnListing('equipos');
    $equiposPayload = array_intersect_key($all, array_flip($columns));

    // Nunca actualices estas columnas por seguridad
    unset(
        $equiposPayload['id'],
        $equiposPayload['created_at'],
        $equiposPayload['updated_at'],
        $equiposPayload['deleted_at']
    );


    // 3) Diff seguro (excluye arrays/relaciones)
    $now = $this->form->except(['monitor_entradas_rows']);
    $old = collect($this->originalSnapshot)->except(['monitor_entradas_rows'])->all();

    $cambios = array_diff_assoc(
        array_map('strval', $now),
        array_map('strval', $old)
    );

    DB::transaction(function () use ($equiposPayload, $cambios) {
        // Guardar SOLO columnas de equipos
        $this->equipo->update($equiposPayload);

        // Guardar tablas relacionadas
        $this->guardarBaterias();
        $this->guardarMonitor();

        // Auditoría
        // if (!empty($cambios)) {
           // $this->registrarAuditoria($cambios);
        //}
    });

    $this->originalSnapshot = $this->form->all();

    $this->baseline = $this->form->snapshotPersistible();
    $this->hasChanges = false;


    $this->dispatch('toast', type: 'success', message: 'Actualizado correctamente');
}



    public function removePuertoUsb(int $index): void
{
    if (!isset($this->puertos_usb[$index])) return;

    unset($this->puertos_usb[$index]);
    $this->puertos_usb = array_values($this->puertos_usb);

    // opcional: si quieres que se refleje al instante en el form
    $this->sincronizarUAlForm();
    $this->recalcChanges();

}

public function removePuertoVideo(int $index): void
{
    if (!isset($this->puertos_video[$index])) return;

    unset($this->puertos_video[$index]);
    $this->puertos_video = array_values($this->puertos_video);

    $this->sincronizarUAlForm();
    $this->recalcChanges();

}

public function removeSlotAlmacenamiento(int $index): void
{
    if (!isset($this->slots_almacenamiento[$index])) return;

    unset($this->slots_almacenamiento[$index]);
    $this->slots_almacenamiento = array_values($this->slots_almacenamiento);

    $this->sincronizarUAlForm();
    $this->recalcChanges();

}



    private function sincronizarUAlForm()
    {
        // Limpiar campos de puertos en el form antes de rellenar
        foreach (array_merge(self::MAP_USB, self::MAP_VIDEO, self::MAP_SLOTS) as $col) $this->form->$col = null;

        foreach ($this->puertos_usb as $u) { $col = self::MAP_USB[$u['tipo']] ?? null; if ($col) $this->form->$col = $u['cantidad']; }
        foreach ($this->puertos_video as $v) { $col = self::MAP_VIDEO[$v['tipo']] ?? null; if ($col) $this->form->$col = $v['cantidad']; }
        foreach ($this->slots_almacenamiento as $s) { $col = self::MAP_SLOTS[$s['tipo']] ?? null; if ($col) $this->form->$col = $s['cantidad']; }

        // Detalles
        $esteticos = $this->detalles_esteticos_checks;
        if ($this->detalles_esteticos_otro) $esteticos[] = $this->detalles_esteticos_otro;
        $this->form->detalles_esteticos = implode(', ', array_filter($esteticos));

        $funcionales = $this->detalles_funcionamiento_checks;
        if ($this->detalles_funcionamiento_otro) $funcionales[] = $this->detalles_funcionamiento_otro;
        $this->form->detalles_funcionamiento = implode(', ', array_filter($funcionales));
    }



    private function pantallaIntegradaPorTipo(): bool
{
    $tipo = strtoupper((string) ($this->form->tipo_equipo ?? ''));
    return in_array($tipo, ['LAPTOP', '2 EN 1', 'ALL IN ONE', 'TABLET'], true);
}

private function pantallaExternaPorTipo(): bool
{
    $tipo = strtoupper((string) ($this->form->tipo_equipo ?? ''));
    return in_array($tipo, ['ESCRITORIO', 'MICRO PC', 'GAMER'], true);
}

private function pantallaDefinidaPorTipo(): bool
{
    return $this->pantallaIntegradaPorTipo() || $this->pantallaExternaPorTipo();
}


private function guardarMonitor(): void
{
    // Si el tipo no define pantalla, no tocamos nada (o podrías borrar si prefieres)
    if (!$this->pantallaDefinidaPorTipo()) {
        return;
    }

    // =========================
    // INTEGRADA (por tipo)
    // =========================
    if ($this->pantallaIntegradaPorTipo()) {

        // Reset in_*
        $resetIn = [];
        foreach (self::MAP_MONITOR_IN as $label => $col) $resetIn[$col] = 0;

        // En integrada, no aplica "monitor incluido"
        $this->form->monitor_incluido = 'NO';

        EquipoMonitor::updateOrCreate(
            ['equipo_id' => $this->equipo->id],
            array_merge([
                'origen_pantalla' => 'INTEGRADA',
                'incluido'        => 1,
                'pulgadas'        => $this->form->pantalla_pulgadas ?: null,
                'resolucion'      => $this->form->pantalla_resolucion ?: null,
                'es_touch'        => (int)((bool)$this->form->pantalla_es_touch),

                // 🔥 limpiar todo lo externo
                'detalles_esteticos_checks'      => null,
                'detalles_esteticos_otro'        => null,
                'detalles_funcionamiento_checks' => null,
                'detalles_funcionamiento_otro'   => null,
            ], $resetIn)
        );

        return;
    }

    // =========================
    // EXTERNA (por tipo)
    // =========================
    // Si es externa pero NO incluye monitor => borramos el registro
    // EXTERNA (por tipo)
    $incluido = (($this->form->monitor_incluido ?? 'NO') === 'SI');

    // Reset in_*
    $resetIn = [];
    foreach (self::MAP_MONITOR_IN as $label => $col) $resetIn[$col] = 0;

    if (!$incluido) {
        // ✅ Mantener registro pero “vacío”
        EquipoMonitor::updateOrCreate(
            ['equipo_id' => $this->equipo->id],
            array_merge([
                'origen_pantalla' => 'EXTERNA',
                'incluido'        => 0,

                // datos externos en NULL
                'pulgadas'   => null,
                'resolucion' => null,
                'es_touch'   => 0,

                // detalles en NULL
                'detalles_esteticos_checks'      => null,
                'detalles_esteticos_otro'        => null,
                'detalles_funcionamiento_checks' => null,
                'detalles_funcionamiento_otro'   => null,
            ], $resetIn)
        );

        return;
    }


    // rows -> in_*
    $inPayload = [];
    foreach (self::MAP_MONITOR_IN as $label => $col) $inPayload[$col] = 0;

    foreach (($this->form->monitor_entradas_rows ?? []) as $row) {
        $tipo = $row['tipo'] ?? null;
        $cantidad = (int)($row['cantidad'] ?? 0);
        if (!$tipo || $cantidad <= 0) continue;

        $col = self::MAP_MONITOR_IN[$tipo] ?? null;
        if ($col) $inPayload[$col] = $cantidad;
    }

    EquipoMonitor::updateOrCreate(
        ['equipo_id' => $this->equipo->id],
        array_merge([
            'origen_pantalla' => 'EXTERNA',
            'incluido'        => 1,
            'pulgadas'        => $this->form->monitor_pulgadas ?: null,
            'resolucion'      => $this->form->monitor_resolucion ?: null,
            'es_touch'        => (int)((bool)$this->form->monitor_es_touch),

            'detalles_esteticos_checks'      => $this->form->monitor_detalles_esteticos_checks ?: null,
            'detalles_esteticos_otro'        => $this->form->monitor_detalles_esteticos_otro ?: null,
            'detalles_funcionamiento_checks' => $this->form->monitor_detalles_funcionamiento_checks ?: null,
            'detalles_funcionamiento_otro'   => $this->form->monitor_detalles_funcionamiento_otro ?: null,
        ], $inPayload)
    );
}

public function updatedFormTipoEquipo($value): void
{
    // Si ahora es integrada, limpia campos externos del FORM
    if ($this->pantallaIntegradaPorTipo()) {
        $this->form->monitor_incluido = 'NO';
        $this->form->monitor_pulgadas = null;
        $this->form->monitor_resolucion = null;
        $this->form->monitor_es_touch = false;
        $this->form->monitor_entradas_rows = [];

        $this->form->monitor_detalles_esteticos_checks = '';
        $this->form->monitor_detalles_esteticos_otro = '';
        $this->form->monitor_detalles_funcionamiento_checks = '';
        $this->form->monitor_detalles_funcionamiento_otro = '';
    }

    // Si ahora es externa, limpia pantalla integrada del FORM (opcional)
    if ($this->pantallaExternaPorTipo()) {
        $this->form->pantalla_pulgadas = null;
        $this->form->pantalla_resolucion = null;
        $this->form->pantalla_es_touch = false;
    }
}



public function updatedFormRamSinSlots($value): void
{
    $value = (bool) $value;

    if ($value) {
        // ✅ Si NO tiene slots => “totalmente soldada” por lógica
        $this->form->ram_es_soldada = true;
        $this->form->ram_slots_totales = '0';
        $this->form->ram_expansion_max = '0 GB';

        // opcional: si manejas número de slots usados, etc., ponlos en 0 también
        // $this->form->ram_slots_usados = '0';
    } else {
        // ✅ Si vuelve a tener slots, NO forzamos nada duro,
        // solo evitamos inconsistencias obvias.
        if (($this->form->ram_slots_totales ?? null) === '0') {
            $this->form->ram_slots_totales = null; // o '1' si prefieres default
        }
        if (($this->form->ram_expansion_max ?? null) === '0 GB') {
            $this->form->ram_expansion_max = null;
        }
        // Nota: NO apagamos ram_es_soldada automáticamente, porque puede seguir siendo parcialmente soldada.
    }
}



public function addMonitorEntrada(): void
{
    $this->form->monitor_entradas_rows ??= [];

    $this->form->monitor_entradas_rows[] = [
        'tipo' => '',
        'cantidad' => 1,
    ];

    $this->recalcChanges();

}

public function removeMonitorEntrada(int $index): void
{
    if (!isset($this->form->monitor_entradas_rows[$index])) return;

    unset($this->form->monitor_entradas_rows[$index]);
    $this->form->monitor_entradas_rows = array_values($this->form->monitor_entradas_rows);

    $this->recalcChanges();

}



public array $monitorEntradasOptions = [];



    // ... Métodos de catálogos y agregar/quitar filas (mantener igual que el tuyo)
private function cargarCatalogos()
{
    $this->lotes = Lote::orderByDesc('id')->get(['id', 'nombre_lote'])->toArray();
    $this->proveedores = Proveedor::orderBy('nombre_empresa')->get(['id', 'nombre_empresa'])->toArray();

    // ✅ para el select de entradas del monitor
    $this->monitorEntradasOptions = array_keys(self::MAP_MONITOR_IN);
}
    public function render() { return view('livewire.inventario.editar-equipo'); }
}