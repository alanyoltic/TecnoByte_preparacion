<div>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10 space-y-6">

        <x-toast />

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TOPBAR DINÁMICO                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <x-topbar
            title="Historial de Compras"
            chip="Preparación · Inventario"
            description="Consulta el historial de compras de piezas con trazabilidad de proveedor."
        >
            <x-slot name="right">
                <a href="{{ route('preparacion.catalogo-piezas') }}"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2
                           bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                           text-white text-xs font-semibold shadow-md shadow-blue-800/40
                           hover:shadow-blue-500/60 hover:-translate-y-0.5 transition-all duration-200">
                    🛒 Registrar compra (ir a catálogo)
                </a>
            </x-slot>
        </x-topbar>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- LISTA DE COMPRAS                                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

            {{-- Filtros --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/70 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md px-5 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            placeholder="Buscar por proveedor o folio..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-2xl
                                   bg-white/80 dark:bg-slate-900/60
                                   border border-white/60 dark:border-slate-700/70
                                   text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-blue-500/70 backdrop-blur-xl">
                    </div>
                    <select wire:model.live="filtroProveedor"
                        class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                               border border-white/60 dark:border-slate-600/70
                               text-sm text-slate-900 dark:text-slate-100
                               focus:outline-none focus:ring-2 focus:ring-blue-500/70 px-3 py-2.5">
                        <option value="">Todos los proveedores</option>
                        @foreach($this->proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tabla de compras --}}
            <div class="rounded-2xl bg-white/80 dark:bg-slate-950/80 border border-slate-200/80 dark:border-white/10
                        backdrop-blur-xl shadow-md overflow-hidden">

                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Historial de compras</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $this->compras->total() }} registro(s)</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-slate-100 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800/80">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Proveedor</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Folio</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Lote</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300 text-center">Tipos de pieza</th>
                                <th class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-300">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->compras as $compra)
                                <tr class="border-b border-slate-200 dark:border-slate-800/80
                                           hover:bg-white/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $compra->fecha_compra->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $compra->proveedor->nombre_empresa }}</p>
                                        @if($compra->proveedor->abreviacion)
                                            <p class="text-xs text-slate-400">{{ $compra->proveedor->abreviacion }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ $compra->folio ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $compra->lote?->nombre ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                                                     border border-indigo-300/50">
                                            {{ $compra->items_count }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $compra->registradoPor?->nombre ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                                        No hay compras registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
                    {{ $this->compras->links() }}
                </div>
            </div>


        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- MODAL: NUEVO PROVEEDOR                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($modalProveedor)
            <div class="fixed inset-0 z-50 flex items-center justify-center"
                 x-data @keydown.escape.window="$wire.cerrarModalProveedor()">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                     wire:click="cerrarModalProveedor"></div>

                <div class="relative w-[94%] max-w-md rounded-2xl border border-white/10
                            bg-white/90 dark:bg-slate-950/90 backdrop-blur-2xl
                            shadow-2xl shadow-slate-950/60 px-6 py-6 space-y-4">

                    <div class="flex items-center justify-between">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-slate-50">Nuevo proveedor</h4>
                        <button wire:click="cerrarModalProveedor"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition text-xl leading-none">×</button>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Nombre de la empresa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model.live="proveedorNombre"
                                placeholder="Ej: Distribuidora Tecnológica del Norte..."
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-blue-500/70 outline-none">
                            @error('proveedorNombre') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Abreviación <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input type="text" wire:model="proveedorAbreviacion"
                                placeholder="Ej: DTN, Tech-MX..." maxlength="20"
                                class="w-full rounded-xl px-3 py-2.5 text-sm
                                       bg-white/70 dark:bg-slate-900/40
                                       border border-slate-300/80 dark:border-slate-700
                                       text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                       focus:ring-2 focus:ring-blue-500/70 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Correo contacto
                                </label>
                                <input type="email" wire:model="proveedorEmail"
                                    placeholder="correo@empresa.com"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                           focus:ring-2 focus:ring-blue-500/70 outline-none">
                                @error('proveedorEmail') <p class="text-xs text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Teléfono
                                </label>
                                <input type="text" wire:model="proveedorTelefono"
                                    placeholder="81-1234-5678"
                                    class="w-full rounded-xl px-3 py-2.5 text-sm
                                           bg-white/70 dark:bg-slate-900/40
                                           border border-slate-300/80 dark:border-slate-700
                                           text-slate-900 dark:text-slate-100 placeholder:text-slate-400
                                           focus:ring-2 focus:ring-blue-500/70 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button wire:click="cerrarModalProveedor"
                            class="inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-medium
                                   border border-slate-300/80 dark:border-slate-700
                                   bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        <button wire:click="guardarProveedor"
                            wire:loading.attr="disabled" wire:target="guardarProveedor"
                            class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold
                                   bg-blue-600 hover:bg-blue-500 text-white
                                   shadow-md shadow-blue-800/40 hover:shadow-blue-500/60
                                   hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardarProveedor">Guardar proveedor</span>
                            <span wire:loading wire:target="guardarProveedor">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-tb-background>
</div>
