<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6"
         x-data="{ tab: 'listado' }"
         @notify.window="$dispatch('notify', $event.detail)"
    >
        <x-toast />

        {{-- ===== TOPBAR GLASS ===== --}}
        <x-topbar
            title="Directorio de Clientes"
            chip="Ventas · Cartera Comercial"
            description="Gestiona el padrón de clientes particulares y personas morales con datos fiscales SAT."
        >
            <div class="flex items-center gap-3">
                @can('ventas.clientes.crear')
                    <button
                        wire:click="abrirCrear"
                        id="btn-nuevo-cliente"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold
                               bg-[#FF9521] hover:bg-[#e0821b] text-slate-950
                               shadow-lg shadow-[#FF9521]/20 active:scale-95 transition-all duration-150"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Nuevo Cliente</span>
                    </button>
                @endcan
            </div>
        </x-topbar>

        {{-- ===== FILTROS GLASS ===== --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </span>
                <input
                    wire:model.live.debounce.350ms="busqueda"
                    id="filtro-busqueda-clientes"
                    type="text"
                    placeholder="Buscar por nombre, RFC, correo, teléfono…"
                    class="w-full pl-11 pr-4 py-2.5 rounded-2xl text-sm
                           bg-white/80 dark:bg-slate-900/60
                           border border-slate-200/80 dark:border-white/10
                           text-slate-900 dark:text-slate-100
                           placeholder-slate-400 dark:placeholder-slate-500
                           focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent
                           backdrop-blur-xl transition-all"
                />
            </div>

            <select
                wire:model.live="filtroTipo"
                id="filtro-tipo-cliente"
                class="px-4 py-2.5 rounded-2xl text-sm
                       bg-white/80 dark:bg-slate-900/60
                       border border-slate-200/80 dark:border-white/10
                       text-slate-800 dark:text-slate-200
                       focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent
                       backdrop-blur-xl transition-all">
                <option value="">Todos los tipos de persona</option>
                <option value="FISICA">Persona Física</option>
                <option value="MORAL">Persona Moral</option>
            </select>
        </div>

        {{-- ===== TABLA GLASS ===== --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-white/10
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 dark:border-white/10
                                   bg-slate-100/60 dark:bg-slate-950/40 text-left">
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300">Cliente / Razón Social</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden md:table-cell">RFC SAT</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden lg:table-cell">Contacto</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hidden xl:table-cell">Vendedor Asignado</th>
                            <th class="px-6 py-4 font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-white/5">
                        @forelse($clientes as $cliente)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150 group">

                                {{-- Nombre + tipo --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        {{-- Avatar inicial --}}
                                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 font-black text-sm
                                                    {{ $cliente->tipo_persona === 'MORAL'
                                                        ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30'
                                                        : 'bg-[#FF9521]/20 text-[#FF9521] border border-[#FF9521]/30' }}">
                                            {{ $cliente->inicial }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                                {{ $cliente->display_name }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full
                                                            {{ $cliente->tipo_persona === 'MORAL'
                                                                ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20'
                                                                : 'bg-[#FF9521]/10 text-[#FF9521] border border-[#FF9521]/20' }}">
                                                    {{ $cliente->tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                                </span>
                                                @if($cliente->municipio || $cliente->estado)
                                                    <span class="text-xs text-slate-400">
                                                        {{ collect([$cliente->municipio, $cliente->estado])->filter()->implode(', ') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- RFC --}}
                                <td class="px-6 py-4 hidden md:table-cell">
                                    @if($cliente->rfc)
                                        <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                            {{ $cliente->rfc }}
                                        </span>
                                    @else
                                        <span class="text-slate-500 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Contacto --}}
                                <td class="px-6 py-4 hidden lg:table-cell text-xs space-y-1">
                                    @if($cliente->correo)
                                        <div class="text-slate-700 dark:text-slate-300 flex items-center gap-1.5 font-medium">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $cliente->correo }}
                                        </div>
                                    @endif
                                    @if($cliente->telefono)
                                        <div class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            {{ $cliente->telefono }}
                                        </div>
                                    @endif
                                    @if(!$cliente->correo && !$cliente->telefono)
                                        <span class="text-slate-500 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Vendedor --}}
                                <td class="px-6 py-4 hidden xl:table-cell text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $cliente->vendedor?->nombre ? $cliente->vendedor->nombre . ' ' . $cliente->vendedor->apellido_paterno : '—' }}
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('ventas.clientes.editar')
                                            <button
                                                wire:click="abrirEditar({{ $cliente->id }})"
                                                class="p-2 rounded-xl text-slate-400 hover:text-[#FF9521] hover:bg-[#FF9521]/10 transition-colors"
                                                title="Editar cliente"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('ventas.clientes.eliminar')
                                            <button
                                                wire:click="confirmarEliminar({{ $cliente->id }})"
                                                class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                                                title="Eliminar cliente"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                                        <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <p class="text-sm font-medium">No se encontraron clientes que coincidan con la búsqueda.</p>
                                        @can('ventas.clientes.crear')
                                            <button wire:click="abrirCrear"
                                                    class="text-[#FF9521] hover:underline text-xs font-bold">
                                                + Registrar nuevo cliente
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="pt-2">{{ $clientes->links() }}</div>

        {{-- ================================================================
             MODAL CREAR / EDITAR GLASS
        ================================================================ --}}
        @if($modalAbierto)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-data x-init="$el.closest('.fixed').querySelector('[id=modal-cliente]').scrollTop = 0">

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md"
                     wire:click="cerrarModal"></div>

                {{-- Panel --}}
                <div id="modal-cliente"
                     class="relative w-full max-w-3xl max-h-[92vh] overflow-y-auto
                            rounded-3xl shadow-2xl
                            bg-white/95 dark:bg-slate-900/95
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-2xl scroll-smooth">

                    {{-- Header modal --}}
                    <div class="sticky top-0 z-10 flex items-center justify-between
                                px-6 sm:px-8 py-5 border-b border-slate-200/80 dark:border-white/10
                                bg-white/95 dark:bg-slate-900/95 backdrop-blur-md">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF9521]"></span>
                                {{ $clienteId ? 'Editar Cliente' : 'Nuevo Cliente' }}
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Captura los datos fiscales o particulares del cliente.</p>
                        </div>
                        <button wire:click="cerrarModal"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="guardar" class="p-6 sm:p-8 space-y-6">

                        {{-- ── TIPO DE PERSONA ── --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                                Tipo de Persona <span class="text-[#FF9521]">*</span>
                            </label>
                            <div class="flex rounded-2xl overflow-hidden border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-950 p-1">
                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer py-2.5 rounded-xl text-xs sm:text-sm font-bold transition
                                              {{ $tipo_persona === 'FISICA'
                                                  ? 'bg-[#FF9521] text-slate-950 shadow-md shadow-[#FF9521]/20'
                                                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                    <input type="radio" wire:model.live="tipo_persona" value="FISICA" class="sr-only">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Persona Física
                                </label>
                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer py-2.5 rounded-xl text-xs sm:text-sm font-bold transition
                                              {{ $tipo_persona === 'MORAL'
                                                  ? 'bg-[#FF9521] text-slate-950 shadow-md shadow-[#FF9521]/20'
                                                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                    <input type="radio" wire:model.live="tipo_persona" value="MORAL" class="sr-only">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Persona Moral
                                </label>
                            </div>
                        </div>

                        {{-- ── IDENTIDAD ── --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if($tipo_persona === 'FISICA')
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nombre(s) <span class="text-[#FF9521]">*</span></label>
                                    <input wire:model="nombres" id="campo-nombres" type="text"
                                           class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Ej: Juan Carlos"/>
                                    @error('nombres')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Apellidos</label>
                                    <input wire:model="apellidos" id="campo-apellidos" type="text"
                                           class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Ej: Pérez Gómez"/>
                                    @error('apellidos')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            @else
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Razón Social <span class="text-[#FF9521]">*</span></label>
                                    <input wire:model="razon_social" id="campo-razon-social" type="text"
                                           class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Ej: Tecnologías Digitales del Centro S.A. de C.V."/>
                                    @error('razon_social')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            @endif
                        </div>

                        {{-- ── FISCAL ── --}}
                        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                                    bg-slate-100/60 dark:bg-slate-950/40 p-5 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#FF9521]">Datos Fiscales (SAT)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">RFC</label>
                                    <input wire:model="rfc" id="campo-rfc" type="text" maxlength="13"
                                           class="w-full px-4 py-2.5 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="XAXX010101000"/>
                                    @error('rfc')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Régimen Fiscal</label>
                                    <select wire:model="regimen_fiscal" id="campo-regimen"
                                            class="w-full px-4 py-2.5 rounded-2xl text-xs sm:text-sm border border-slate-300 dark:border-slate-700
                                                   bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200
                                                   focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition">
                                        <option value="">— Sin régimen fiscal asignado —</option>
                                        @foreach($opcionesRegimen as $codigo => $desc)
                                            <option value="{{ $codigo }}">{{ $desc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Uso de CFDI</label>
                                    <select wire:model="uso_cfdi" id="campo-uso-cfdi"
                                            class="w-full px-4 py-2.5 rounded-2xl text-xs sm:text-sm border border-slate-300 dark:border-slate-700
                                                   bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200
                                                   focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition">
                                        <option value="">— Sin uso de CFDI especificado —</option>
                                        @foreach($opcionesUsoCfdi as $codigo => $desc)
                                            <option value="{{ $codigo }}">{{ $desc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ── COMERCIAL ── --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Vendedor asignado</label>
                                <select wire:model="vendedor_id" id="campo-vendedor"
                                        class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                               bg-slate-50 dark:bg-slate-950/70 text-slate-700 dark:text-slate-200
                                               focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition">
                                    <option value="">— Sin asignar —</option>
                                    @foreach($vendedores as $v)
                                        <option value="{{ $v->id }}">{{ $v->nombre }} {{ $v->apellido_paterno }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">¿Cómo se enteró de nosotros?</label>
                                <select wire:model="como_se_entero" id="campo-como-entero"
                                        class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                               bg-slate-50 dark:bg-slate-950/70 text-slate-700 dark:text-slate-200
                                               focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition">
                                    <option value="">— Seleccionar medio —</option>
                                    @foreach($opcionesComoSeEntero as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ── CONTACTO ── --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Correo Electrónico</label>
                                <input wire:model="correo" id="campo-correo" type="email"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="correo@ejemplo.com"/>
                                @error('correo')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Teléfono</label>
                                <input wire:model="telefono" id="campo-telefono" type="text"
                                       class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                              bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                              focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                       placeholder="442 123 4567"/>
                            </div>
                        </div>

                        {{-- ── DIRECCIÓN ── --}}
                        <div class="rounded-3xl border border-slate-200/80 dark:border-white/10
                                    bg-slate-100/60 dark:bg-slate-950/40 p-5 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#FF9521]">Dirección de Entrega / Fiscal</p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">País</label>
                                    <input wire:model="pais" id="campo-pais" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="México"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Estado</label>
                                    <input wire:model="estado" id="campo-estado" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Querétaro"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Municipio</label>
                                    <input wire:model="municipio" id="campo-municipio" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Corregidora"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Localidad</label>
                                    <input wire:model="localidad" id="campo-localidad" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Localidad"/>
                                </div>
                                <div class="col-span-2 sm:col-span-3">
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Calle</label>
                                    <input wire:model="calle" id="campo-calle" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Nombre de la calle"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Colonia</label>
                                    <input wire:model="colonia" id="campo-colonia" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Colonia"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Ext</label>
                                    <input wire:model="no_ext" id="campo-no-ext" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="123"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Int</label>
                                    <input wire:model="no_int" id="campo-no-int" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="A"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Código Postal</label>
                                    <input wire:model="codigo_postal" id="campo-cp" type="text" maxlength="5"
                                           class="w-full px-4 py-2 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="76900"/>
                                    @error('codigo_postal')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Cód. Colonia <span class="text-slate-400 normal-case font-normal">(SAT)</span></label>
                                    <input wire:model="codigo_colonia" id="campo-cod-colonia" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Opcional"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Cód. Localidad <span class="text-slate-400 normal-case font-normal">(SAT)</span></label>
                                    <input wire:model="codigo_localidad" id="campo-cod-localidad" type="text"
                                           class="w-full px-4 py-2 rounded-2xl text-sm font-mono border border-slate-300 dark:border-slate-700
                                                  bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100
                                                  focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition"
                                           placeholder="Opcional"/>
                                </div>
                            </div>
                        </div>

                        {{-- ── NOTAS ── --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Notas / Observaciones</label>
                            <textarea wire:model="notas" id="campo-notas" rows="3"
                                      class="w-full px-4 py-2.5 rounded-2xl text-sm border border-slate-300 dark:border-slate-700
                                             bg-slate-50 dark:bg-slate-950/70 text-slate-900 dark:text-slate-100
                                             focus:outline-none focus:ring-2 focus:ring-[#FF9521] focus:border-transparent transition resize-none"
                                      placeholder="Observaciones comerciales o requerimientos del cliente…"></textarea>
                        </div>

                        {{-- ── ACCIONES ── --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80 dark:border-white/10">
                            <button type="button" wire:click="cerrarModal"
                                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300
                                           bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                Cancelar
                            </button>
                            <button type="submit" id="btn-guardar-cliente"
                                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-950
                                           bg-[#FF9521] hover:bg-[#e0821b]
                                           shadow-lg shadow-[#FF9521]/20
                                           transition-all duration-150 active:scale-95"
                                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                                <span wire:loading.remove wire:target="guardar">
                                    {{ $clienteId ? 'Guardar Cambios' : 'Registrar Cliente' }}
                                </span>
                                <span wire:loading wire:target="guardar">Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ================================================================
             MODAL ELIMINAR GLASS
        ================================================================ --}}
        @if($modalEliminar)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md"
                     wire:click="$set('modalEliminar', false)"></div>
                <div class="relative w-full max-w-sm rounded-3xl bg-white/95 dark:bg-slate-900/95
                            border border-slate-200 dark:border-white/10 shadow-2xl p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">¿Eliminar cliente?</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Esta acción moverá el cliente a la papelera del sistema.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button wire:click="$set('modalEliminar', false)"
                                class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold
                                       bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300
                                       hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Cancelar
                        </button>
                        <button wire:click="eliminar" id="btn-confirmar-eliminar"
                                class="flex-1 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white
                                       bg-rose-600 hover:bg-rose-500 transition active:scale-95 shadow-md shadow-rose-900/20">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-tb-background>
