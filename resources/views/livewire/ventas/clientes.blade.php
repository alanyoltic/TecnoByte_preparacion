<div class="p-6 space-y-5"
    x-data="{ tab: 'listado' }"
    @notify.window="$dispatch('notify', $event.detail)"
>

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Clientes</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Gestiona el directorio de clientes del módulo de Ventas.
            </p>
        </div>

        @can('ventas.clientes.crear')
        <button
            wire:click="abrirCrear"
            id="btn-nuevo-cliente"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                   bg-gradient-to-r from-emerald-600 to-teal-600 text-white
                   shadow-lg shadow-emerald-500/30
                   hover:from-emerald-500 hover:to-teal-500
                   transition-all duration-200 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Cliente
        </button>
        @endcan
    </div>

    {{-- ===== FILTROS ===== --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </span>
            <input
                wire:model.live.debounce.350ms="busqueda"
                id="filtro-busqueda-clientes"
                type="text"
                placeholder="Buscar por nombre, RFC, correo, teléfono…"
                class="w-full pl-9 pr-4 py-2 rounded-xl text-sm
                       bg-white/60 dark:bg-slate-800/60
                       border border-slate-200 dark:border-slate-700
                       text-slate-800 dark:text-slate-100
                       placeholder-slate-400 dark:placeholder-slate-500
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/50
                       backdrop-blur-md transition"
            />
        </div>

        <select
            wire:model.live="filtroTipo"
            id="filtro-tipo-cliente"
            class="px-3 py-2 rounded-xl text-sm
                   bg-white/60 dark:bg-slate-800/60
                   border border-slate-200 dark:border-slate-700
                   text-slate-700 dark:text-slate-200
                   focus:outline-none focus:ring-2 focus:ring-emerald-500/50
                   backdrop-blur-md transition">
            <option value="">Todos los tipos</option>
            <option value="FISICA">Persona Física</option>
            <option value="MORAL">Persona Moral</option>
        </select>
    </div>

    {{-- ===== TABLA ===== --}}
    <div class="overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50
                bg-white/60 dark:bg-slate-900/50 backdrop-blur-md shadow-xl">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200/60 dark:border-slate-700/50
                           bg-slate-50/80 dark:bg-slate-800/60 text-left">
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300">Cliente</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden md:table-cell">RFC</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden lg:table-cell">Contacto</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 hidden xl:table-cell">Vendedor</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-300 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-700/40">
                @forelse($clientes as $cliente)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150 group">

                    {{-- Nombre + tipo --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            {{-- Avatar inicial --}}
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm
                                        {{ $cliente->tipo_persona === 'MORAL'
                                            ? 'bg-violet-500/20 text-violet-400'
                                            : 'bg-emerald-500/20 text-emerald-400' }}">
                                {{ $cliente->inicial }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-100 leading-tight">
                                    {{ $cliente->nombre_completo }}
                                </p>
                                <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 rounded text-[0.65rem] font-medium
                                             {{ $cliente->tipo_persona === 'MORAL'
                                                 ? 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300'
                                                 : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' }}">
                                    {{ $cliente->tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- RFC --}}
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="font-mono text-xs text-slate-600 dark:text-slate-300">
                            {{ $cliente->rfc ?? '—' }}
                        </span>
                    </td>

                    {{-- Contacto --}}
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <div class="space-y-0.5">
                            @if($cliente->correo)
                            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $cliente->correo }}
                            </p>
                            @endif
                            @if($cliente->telefono)
                            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $cliente->telefono }}
                            </p>
                            @endif
                            @if(!$cliente->correo && !$cliente->telefono)
                            <span class="text-xs text-slate-400">Sin contacto</span>
                            @endif
                        </div>
                    </td>

                    {{-- Vendedor --}}
                    <td class="px-4 py-3 hidden xl:table-cell">
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $cliente->vendedor?->nombre_inicial ?? '—' }}
                        </span>
                    </td>

                    {{-- Acciones --}}
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            @can('ventas.clientes.editar')
                            <button
                                wire:click="abrirEditar({{ $cliente->id }})"
                                title="Editar"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500
                                       hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endcan

                            @can('ventas.clientes.eliminar')
                            <button
                                wire:click="confirmarEliminar({{ $cliente->id }})"
                                title="Eliminar"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500
                                       hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
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
                    <td colspan="5" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                            <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm">No hay clientes que coincidan con la búsqueda.</p>
                            @can('ventas.clientes.crear')
                            <button wire:click="abrirCrear"
                                    class="text-emerald-500 hover:underline text-sm font-medium">
                                + Registrar primer cliente
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div>{{ $clientes->links() }}</div>


    {{-- ================================================================
         MODAL CREAR / EDITAR
    ================================================================ --}}
    @if($modalAbierto)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-init="$el.closest('.fixed').querySelector('[id=modal-cliente]').scrollTop = 0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="cerrarModal"></div>

        {{-- Panel --}}
        <div id="modal-cliente"
             class="relative w-full max-w-3xl max-h-[92vh] overflow-y-auto
                    rounded-3xl shadow-2xl
                    bg-white dark:bg-slate-900
                    border border-slate-200/60 dark:border-slate-700/50
                    scroll-smooth">

            {{-- Header modal --}}
            <div class="sticky top-0 z-10 flex items-center justify-between
                        px-6 py-4 border-b border-slate-100 dark:border-slate-800
                        bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $clienteId ? 'Editar Cliente' : 'Nuevo Cliente' }}
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Todos los campos son opcionales excepto el tipo de persona.</p>
                </div>
                <button wire:click="cerrarModal"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="guardar" class="p-6 space-y-6">

                {{-- ── TIPO DE PERSONA ── --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        Tipo de Persona <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer py-2.5 text-sm font-medium transition
                                      {{ $tipo_persona === 'FISICA'
                                          ? 'bg-emerald-500 text-white'
                                          : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            <input type="radio" wire:model.live="tipo_persona" value="FISICA" class="sr-only">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Persona Física
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer py-2.5 text-sm font-medium transition border-l border-slate-200 dark:border-slate-700
                                      {{ $tipo_persona === 'MORAL'
                                          ? 'bg-violet-500 text-white'
                                          : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
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
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nombre(s)</label>
                        <input wire:model="nombres" id="campo-nombres" type="text"
                               class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                      bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                               placeholder="Nombre(s)"/>
                        @error('nombres')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Apellidos</label>
                        <input wire:model="apellidos" id="campo-apellidos" type="text"
                               class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                      bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                               placeholder="Apellidos"/>
                        @error('apellidos')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @else
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Razón Social</label>
                        <input wire:model="razon_social" id="campo-razon-social" type="text"
                               class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                      bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition"
                               placeholder="Nombre de la empresa o razón social"/>
                        @error('razon_social')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif
                </div>

                {{-- ── FISCAL ── --}}
                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/50
                            bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Datos Fiscales</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">RFC</label>
                            <input wire:model="rfc" id="campo-rfc" type="text" maxlength="13"
                                   class="w-full px-3 py-2 rounded-xl text-sm font-mono border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 uppercase
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="XAXX010101000"/>
                            @error('rfc')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Régimen Fiscal</label>
                            <select wire:model="regimen_fiscal" id="campo-regimen"
                                    class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                           bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition">
                                <option value="">— Sin régimen fiscal —</option>
                                @foreach($opcionesRegimen as $codigo => $desc)
                                    <option value="{{ $codigo }}">{{ $desc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Uso de CFDI</label>
                            <select wire:model="uso_cfdi" id="campo-uso-cfdi"
                                    class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                           bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition">
                                <option value="">— Sin uso CFDI —</option>
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
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Vendedor asignado</label>
                        <select wire:model="vendedor_id" id="campo-vendedor"
                                class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                       bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition">
                            <option value="">— Sin asignar —</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->nombre }} {{ $v->apellido_paterno }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">¿Cómo se enteró de nosotros?</label>
                        <select wire:model="como_se_entero" id="campo-como-entero"
                                class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                       bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition">
                            <option value="">— Seleccionar —</option>
                            @foreach($opcionesComoSeEntero as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ── CONTACTO ── --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Correo Electrónico</label>
                        <input wire:model="correo" id="campo-correo" type="email"
                               class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                      bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                               placeholder="correo@ejemplo.com"/>
                        @error('correo')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Teléfono</label>
                        <input wire:model="telefono" id="campo-telefono" type="text"
                               class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                      bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                               placeholder="442 123 4567"/>
                    </div>
                </div>

                {{-- ── DIRECCIÓN ── --}}
                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/50
                            bg-slate-50/60 dark:bg-slate-800/40 p-4 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dirección</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">País</label>
                            <input wire:model="pais" id="campo-pais" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="México"/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Estado</label>
                            <input wire:model="estado" id="campo-estado" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Querétaro"/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Municipio</label>
                            <input wire:model="municipio" id="campo-municipio" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Corregidora"/>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Localidad</label>
                            <input wire:model="localidad" id="campo-localidad" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Localidad"/>
                        </div>
                        <div class="col-span-2 sm:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Calle</label>
                            <input wire:model="calle" id="campo-calle" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Nombre de la calle"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Colonia</label>
                            <input wire:model="colonia" id="campo-colonia" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Colonia"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Ext</label>
                            <input wire:model="no_ext" id="campo-no-ext" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="123"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">No. Int</label>
                            <input wire:model="no_int" id="campo-no-int" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="A"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Código Postal</label>
                            <input wire:model="codigo_postal" id="campo-cp" type="text" maxlength="5"
                                   class="w-full px-3 py-2 rounded-xl text-sm font-mono border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="76900"/>
                            @error('codigo_postal')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Cód. Colonia <span class="text-slate-400 normal-case font-normal">(SAT)</span></label>
                            <input wire:model="codigo_colonia" id="campo-cod-colonia" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm font-mono border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Opcional"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Cód. Localidad <span class="text-slate-400 normal-case font-normal">(SAT)</span></label>
                            <input wire:model="codigo_localidad" id="campo-cod-localidad" type="text"
                                   class="w-full px-3 py-2 rounded-xl text-sm font-mono border border-slate-200 dark:border-slate-700
                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                                   placeholder="Opcional"/>
                        </div>
                    </div>
                </div>

                {{-- ── NOTAS ── --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Notas</label>
                    <textarea wire:model="notas" id="campo-notas" rows="3"
                              class="w-full px-3 py-2 rounded-xl text-sm border border-slate-200 dark:border-slate-700
                                     bg-white/80 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100
                                     focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition resize-none"
                              placeholder="Observaciones del cliente…"></textarea>
                </div>

                {{-- ── ACCIONES ── --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="cerrarModal"
                            class="px-5 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300
                                   bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-guardar-cliente"
                            class="px-6 py-2 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-r from-emerald-600 to-teal-600
                                   hover:from-emerald-500 hover:to-teal-500
                                   shadow-lg shadow-emerald-500/30
                                   transition-all duration-200 active:scale-95"
                            wire:loading.attr="disabled" wire:loading.class="opacity-70">
                        <span wire:loading.remove wire:target="guardar">
                            {{ $clienteId ? 'Guardar cambios' : 'Registrar Cliente' }}
                        </span>
                        <span wire:loading wire:target="guardar">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif


    {{-- ================================================================
         MODAL ELIMINAR
    ================================================================ --}}
    @if($modalEliminar)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="$set('modalEliminar', false)"></div>
        <div class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900
                    border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">¿Eliminar cliente?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Esta acción puede revertirse desde papelera.</p>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button wire:click="$set('modalEliminar', false)"
                        class="flex-1 px-4 py-2 rounded-xl text-sm font-medium
                               bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300
                               hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Cancelar
                </button>
                <button wire:click="eliminar" id="btn-confirmar-eliminar"
                        class="flex-1 px-4 py-2 rounded-xl text-sm font-semibold text-white
                               bg-rose-500 hover:bg-rose-600 transition active:scale-95">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
