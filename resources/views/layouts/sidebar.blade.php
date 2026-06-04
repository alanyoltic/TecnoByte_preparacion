{{-- resources/views/layouts/sidebar.blade.php --}}

<div
    class="fixed inset-y-0 left-0 z-50 h-screen
           transition-all duration-300 ease-in-out

           bg-gradient-to-b
           from-slate-100/90 via-slate-100/80 to-slate-200/70
           dark:from-slate-950/95 dark:via-slate-950/90 dark:to-slate-900/80

           backdrop-blur-xl
           overflow-hidden"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
>
    {{-- Glows del sidebar --}}
    <div class="pointer-events-none absolute -top-16 -right-10 w-40 h-40
                bg-sky-500/35 dark:bg-sky-500/45 blur-3xl opacity-70"></div>
    <div class="pointer-events-none absolute bottom-0 -left-10 w-32 h-32
                bg-indigo-500/25 dark:bg-indigo-500/35 blur-3xl opacity-60"></div>

    <div class="flex flex-col h-full relative z-10">

        {{-- HEADER / BRAND --}}
        <div class="flex items-center h-16 px-3">
            {{-- Botón hamburguesa --}}
            <button
                @click.stop="sidebarOpen = !sidebarOpen"
                class="p-2.5 rounded-2xl
                       text-slate-500 dark:text-slate-400
                       hover:text-slate-900 dark:hover:text-slate-50
                       hover:bg-white/70 dark:hover:bg-white/10
                       shadow-sm shadow-slate-900/10
                       focus:outline-none
                       transition-all duration-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Logo TecnoByte --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center ml-3"
               x-show="sidebarOpen"
               x-transition.opacity.duration.300ms
            >
                <img
                    x-cloak
                    src="{{ asset('images/logo-tecnobyte.png') }}"
                    alt="TecnoByte"
                    class="object-contain max-w-full
                           w-24 h-24
                           drop-shadow-[0_0_18px_rgba(37,99,235,0.70)]
                           transition-all duration-300"
                />
            </a>
        </div>

        @php
            $linkBase = "group relative flex items-center
                         w-full h-11
                         rounded-2xl
                         px-3 text-[0.80rem] font-medium
                         border border-transparent
                         backdrop-blur-md
                         transition-all duration-200
                         hover:shadow-[0_8px_24px_rgba(15,23,42,0.55)]";

            $iconBase = "w-5 h-5 flex-shrink-0
                         text-slate-400 dark:text-slate-400
                         group-hover:text-blue-400
                         transition-colors duration-200";

            $labelBase = "ml-2 whitespace-nowrap";

            $isDashboard  = request()->routeIs('dashboard')
                || request()->routeIs('preparacion.dashboard');
            $isLotes      = request()->routeIs('lotes.*');
            $isOperaciones = request()->routeIs('preparacion.mi-trabajo')
                || request()->routeIs('preparacion.asignaciones')
                || request()->routeIs('inventario.piezas.solicitudes')
                || request()->routeIs('equipos.caracteristicas');
            $isInventario = (
                request()->routeIs('inventario.*')
                || request()->routeIs('preparacion.inventario.*')
            ) && !request()->routeIs('inventario.piezas.solicitudes');
            $isUsuarios   = request()->routeIs('users.*') || request()->routeIs('register');

            $u = auth()->user();
            $roleSlug = optional($u?->role)->slug;

            // Flags por permiso (globales)
            $puedePrep   = $u && $u->tienePermiso('modulo.preparacion');
            $puedeSis    = $u && $u->tienePermiso('modulo.sistema');

            // Preparacion
            $puedeEquipos      = $u && $u->tienePermiso('prep.equipos.ver');
            $puedeInv          = $u && $u->tienePermiso('prep.inventario.ver');
            $puedeInvGestion   = $u && $u->tienePermiso('prep.inventario.gestion');
            $puedeLotes        = $u && $u->tienePermiso('prep.lotes.ver');
            $puedeVerMisAsignaciones = $u && $u->tienePermiso('prep.equipos.ver') && in_array($roleSlug, ['lider', 'tecnico'], true);

            // Sistema
            $puedeUsuarios      = $u && $u->tienePermiso('sistema.usuarios.ver');
            $puedeUsuariosCrear = $u && $u->tienePermiso('sistema.usuarios.crear');
            $puedeAdminConfig   = $u && $u->tienePermiso('sistema.admin.configuracion');
            $puedeAvisos        = $u && $u->tienePermiso('sistema.avisos.ver');
        @endphp

       {{-- NAV --}}
<nav class="mt-3 flex-1 space-y-1 overflow-y-auto overflow-x-hidden"
    x-data="{
        activeMenu: null,
        setMenu(id){ this.activeMenu = (this.activeMenu === id) ? null : id; },
        closeMenu(){ this.activeMenu = null; },
        closeAll(){
            this.closeMenu();
            window.dispatchEvent(new CustomEvent('tb-close-popovers'));
        },
    }"
    @click.outside="if(sidebarOpen) closeMenu()"
    @keydown.escape.window="closeMenu()"
    @mouseleave.window="closeAll()"
    @blur.window="closeAll()"
    @resize.window="closeAll()"
    @scroll.window="window.dispatchEvent(new CustomEvent('tb-close-popovers'))"
>

    {{-- ===================== DASHBOARD ===================== --}}
    <div class="px-3">
        <a
            href="{{ route('dashboard') }}"
            title="Dashboard"
            class="{{ $linkBase }} {{ $isDashboard
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                   text-white font-semibold
                   drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
                   border-blue-400/70
                   shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20
                    text-slate-700 dark:text-slate-400
                    hover:bg-white/20 dark:hover:bg-slate-900/30
                    hover:text-slate-900 dark:hover:text-white' }}"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'"
        >
            <div class="flex items-center justify-center w-7 h-7">
                <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 15h.01M12 9h.01M9 15h.01M12 15h.01" />
                </svg>
            </div>

            <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>
                Dashboard
            </span>
        </a>
    </div>

    {{-- Registro lateral solo para calidad (link directo) --}}
    @if(optional($u?->role)->slug === 'calidad' || ($u && $u->tienePermiso('prep.calidad.validar')))
    @php $isRegistro = request()->routeIs('preparacion.calidad'); @endphp
    <div class="px-3 mt-2">
        <a href="{{ route('preparacion.calidad') }}"
           title="Registro"
           class="{{ $linkBase }} {{ $isRegistro
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB] text-white font-semibold drop-shadow-[0_0_6px_rgba(99,102,241,0.65)] border-blue-400/70 shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20 text-slate-700 dark:text-slate-400 hover:bg-white/20 dark:hover:bg-slate-900/30 hover:text-slate-900 dark:hover:text-white' }}"
           :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>Registro</span>
            </div>
        </a>
    </div>
    @endif

    {{-- ===================== RECEPCIÓN - LOTES ===================== --}}
    @if($puedeLotes)
    @php
        $user = auth()->user();
        $lotesItems = [
            [
                'label' => 'Lista de Lotes',
                'href'  => route('lotes.editar'),
                'perm'  => 'prep.lotes.ver',
            ],
            [
                'label' => 'Registrar Lote',
                'href'  => route('lotes.registrar'),
                'perm'  => 'prep.lotes.gestion',
            ],
        ];

        $lotesItems = array_values(array_filter(
            $lotesItems,
            fn ($it) => $user && $user->tienePermiso($it['perm'])
        ));
    @endphp

    <div class="w-full mt-3 px-3"
        x-data="{
            id: 'lotes',
            popoverOpen: false,
            popoverStyle: '',
            triggerEl: null,

            isOpen(){ return sidebarOpen && activeMenu === this.id; },

            toggle(e){
                if (sidebarOpen) { 
                    setMenu(this.id); 
                    return; 
                }

                if (this.popoverOpen) {
                    this.popoverOpen = false;
                    return;
                }

                window.dispatchEvent(new CustomEvent('tb-close-popovers'));
                this.triggerEl = e.currentTarget;
                this.popoverOpen = true;
                this.positionPopover();
            },

            positionPopover(){
                this.$nextTick(() => {
                    if (!this.triggerEl) return;
                    const r = this.triggerEl.getBoundingClientRect();
                    const width = 260, gap = 12;
                    let top  = r.top;
                    let left = r.right + gap;
                    const estimatedHeight = 120;
                    const maxTop = window.innerHeight - 12;
                    if (top + estimatedHeight > maxTop) top = Math.max(12, maxTop - estimatedHeight);
                    this.popoverStyle = `top:${top}px; left:${left}px; width:${width}px;`;
                });
            },

            closePopover(){ this.popoverOpen = false; },
            closeAll(){ this.popoverOpen = false; }
        }"
        @tb-close-popovers.window="closePopover()"
        @keydown.escape.window="closeAll()"
        @resize.window="positionPopover()"
        @scroll.window="positionPopover()"
        x-effect="if(sidebarOpen) popoverOpen=false"
    >
        <button
            type="button"
            @click.stop="toggle($event)"
            class="{{ $linkBase }} {{ $isLotes
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                text-white font-semibold
                drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
                border-blue-400/70
                shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20
                    text-slate-700 dark:text-slate-400
                    hover:bg-white/20 dark:hover:bg-slate-900/30
                    hover:text-slate-900 dark:hover:text-white' }}"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'"
            title="Lotes"
        >
            <div class="flex items-center">
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>Lotes</span>
            </div>

            <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
                fill="currentColor" viewBox="0 0 20 20"
                x-show="sidebarOpen" x-transition
                :class="isOpen() ? 'rotate-180' : ''"
                style="transition: transform .2s ease;">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="isOpen()" x-transition class="mt-2 space-y-1 pl-2">
            <div class="rounded-2xl border
                        bg-white/60 dark:bg-slate-950/40
                        border-slate-200/70 dark:border-white/10
                        backdrop-blur-xl overflow-x-hidden">
                @foreach($lotesItems as $it)
                    <a href="{{ $it['href'] }}"
                    class="block px-4 py-2.5 text-[0.80rem]
                            text-slate-700 dark:text-slate-200
                            hover:bg-slate-100/90 dark:hover:bg-slate-800/70
                            hover:text-slate-900 dark:hover:text-white
                            transition-colors duration-150">
                        {{ $it['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="popoverOpen && !sidebarOpen"
                x-transition.opacity.duration.150ms
                @click.outside="popoverOpen=false"
                class="fixed z-[999999] pointer-events-auto"
                :style="popoverStyle"
            >
                <div class="rounded-2xl border
                            bg-white/90 dark:bg-slate-900/95
                            border-slate-200/70 dark:border-slate-700/70
                            shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                            backdrop-blur-xl overflow-hidden">
                    @foreach($lotesItems as $it)
                        <a href="{{ $it['href'] }}"
                        class="block px-4 py-3 text-[0.80rem]
                                text-slate-700 dark:text-slate-200
                                hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                                hover:text-slate-900 dark:hover:text-white
                                transition-colors duration-150">
                            {{ $it['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </template>
    </div>
    @endif

    {{-- ===================== OPERACIONES ===================== --}}
    @if($puedeEquipos)
    @php
        $user = auth()->user();
        $operacionesItems = [
            [
                'label' => 'Mis Asignaciones',
                'href'  => route('preparacion.mi-trabajo'),
                'visible' => $puedeVerMisAsignaciones,
            ],
            [
                'label' => 'Mis Solicitudes de Piezas',
                'href'  => route('inventario.piezas.solicitudes'),
                'visible' => $puedeVerMisAsignaciones,
            ],
            [
                'label' => 'Gestionar Asignaciones',
                'href'  => route('preparacion.asignaciones'),
                'perm'  => 'prep.inventario.gestion',
            ],
            [
                'label' => 'Resumen de Equipos',
                'href'  => route('equipos.caracteristicas'),
                'perm'  => 'prep.inventario.gestion',
            ],
            [
                'label' => 'Registro',
                'href'  => route('preparacion.calidad'),
                'visible' => $roleSlug === 'calidad',
            ],
        ];

        $operacionesItems = array_values(array_filter(
            $operacionesItems,
            fn ($it) => $it['visible'] ?? ($user && $user->tienePermiso($it['perm']))
        ));
    @endphp

    <div class="w-full mt-3 px-3"
        x-data="{
            id: 'operaciones',
            popoverOpen: false,
            popoverStyle: '',
            triggerEl: null,

            isOpen(){ return sidebarOpen && activeMenu === this.id; },

            toggle(e){
                if (sidebarOpen) { 
                    setMenu(this.id); 
                    return; 
                }

                if (this.popoverOpen) {
                    this.popoverOpen = false;
                    return;
                }

                window.dispatchEvent(new CustomEvent('tb-close-popovers'));
                this.triggerEl = e.currentTarget;
                this.popoverOpen = true;
                this.positionPopover();
            },

            positionPopover(){
                this.$nextTick(() => {
                    if (!this.triggerEl) return;
                    const r = this.triggerEl.getBoundingClientRect();
                    const width = 260, gap = 12;
                    let top  = r.top;
                    let left = r.right + gap;
                    const estimatedHeight = 140;
                    const maxTop = window.innerHeight - 12;
                    if (top + estimatedHeight > maxTop) top = Math.max(12, maxTop - estimatedHeight);
                    this.popoverStyle = `top:${top}px; left:${left}px; width:${width}px;`;
                });
            },

            closePopover(){ this.popoverOpen = false; },
            closeAll(){ this.popoverOpen = false; }
        }"
        @tb-close-popovers.window="closePopover()"
        @keydown.escape.window="closeAll()"
        @resize.window="positionPopover()"
        @scroll.window="positionPopover()"
        x-effect="if(sidebarOpen) popoverOpen=false"
    >
        <button
            type="button"
            @click.stop="toggle($event)"
            class="{{ $linkBase }} {{ $isOperaciones
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                text-white font-semibold
                drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
                border-blue-400/70
                shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20
                    text-slate-700 dark:text-slate-400
                    hover:bg-white/20 dark:hover:bg-slate-900/30
                    hover:text-slate-900 dark:hover:text-white' }}"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'"
            title="Operaciones"
        >
            <div class="flex items-center">
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>Operaciones</span>
            </div>

            <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
                fill="currentColor" viewBox="0 0 20 20"
                x-show="sidebarOpen" x-transition
                :class="isOpen() ? 'rotate-180' : ''"
                style="transition: transform .2s ease;">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="isOpen()" x-transition class="mt-2 space-y-1 pl-2">
            <div class="rounded-2xl border
                        bg-white/60 dark:bg-slate-950/40
                        border-slate-200/70 dark:border-white/10
                        backdrop-blur-xl overflow-x-hidden">
                @foreach($operacionesItems as $it)
                    <a href="{{ $it['href'] }}"
                    class="block px-4 py-2.5 text-[0.80rem]
                            text-slate-700 dark:text-slate-200
                            hover:bg-slate-100/90 dark:hover:bg-slate-800/70
                            hover:text-slate-900 dark:hover:text-white
                            transition-colors duration-150">
                        {{ $it['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="popoverOpen && !sidebarOpen"
                x-transition.opacity.duration.150ms
                @click.outside="popoverOpen=false"
                class="fixed z-[999999] pointer-events-auto"
                :style="popoverStyle"
            >
                <div class="rounded-2xl border
                            bg-white/90 dark:bg-slate-900/95
                            border-slate-200/70 dark:border-slate-700/70
                            shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                            backdrop-blur-xl overflow-hidden">
                    @foreach($operacionesItems as $it)
                        <a href="{{ $it['href'] }}"
                        class="block px-4 py-3 text-[0.80rem]
                                text-slate-700 dark:text-slate-200
                                hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                                hover:text-slate-900 dark:hover:text-white
                                transition-colors duration-150">
                            {{ $it['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </template>
    </div>
    @endif

    {{-- ===================== INVENTARIO ===================== --}}
    @if($puedeInv)
    @php
        $user = auth()->user();
        $inventarioItems = [
            [
                'label' => 'Gestión General',
                'href'  => route('inventario.gestion'),
                'perm'  => 'prep.inventario.gestion',
            ],
            [
                'label' => 'Equipos Listos',
                'href'  => route('inventario.listo'),
                'perm'  => 'prep.inventario.ver',
            ],
            [
                'label' => 'Catálogo Piezas',
                'href'  => route('preparacion.catalogo-piezas'),
                'perm'  => 'prep.inventario.gestion',
            ],
            [
                'label' => 'Catálogo Equipos',
                'href'  => route('preparacion.catalogo-equipos'),
                'perm'  => 'prep.inventario.gestion',
            ],
            // 'Compras de Piezas' se accede desde Catálogo Piezas (historial)
            [
                'label' => 'Transferencias',
                'href'  => route('inventario.transferencias'),
                'perm'  => 'prep.transferencias.ver',
            ],
            [
                'label' => 'Gestión Solicitudes',
                'href'  => route('inventario.solicitudes.gestionar'),
                'perm'  => 'prep.inventario.gestion',
            ],
        ];

        $inventarioItems = array_values(array_filter(
            $inventarioItems,
            fn ($it) => $user && $user->tienePermiso($it['perm'])
        ));
    @endphp

    <div class="w-full mt-3 px-3"
        x-data="{
            id: 'inventario',
            popoverOpen: false,
            popoverStyle: '',
            triggerEl: null,

            isOpen(){ return sidebarOpen && activeMenu === this.id; },

            toggle(e){
                if (sidebarOpen) { 
                    setMenu(this.id); 
                    return; 
                }

                if (this.popoverOpen) {
                    this.popoverOpen = false;
                    return;
                }

                window.dispatchEvent(new CustomEvent('tb-close-popovers'));
                this.triggerEl = e.currentTarget;
                this.popoverOpen = true;
                this.positionPopover();
            },

            positionPopover(){
                this.$nextTick(() => {
                    if (!this.triggerEl) return;
                    const r = this.triggerEl.getBoundingClientRect();
                    const width = 260, gap = 12;
                    let top  = r.top;
                    let left = r.right + gap;
                    const estimatedHeight = 280;
                    const maxTop = window.innerHeight - 12;
                    if (top + estimatedHeight > maxTop) top = Math.max(12, maxTop - estimatedHeight);
                    this.popoverStyle = `top:${top}px; left:${left}px; width:${width}px;`;
                });
            },

            closePopover(){ this.popoverOpen = false; },
            closeAll(){ this.popoverOpen = false; }
        }"
        @tb-close-popovers.window="closePopover()"
        @keydown.escape.window="closeAll()"
        @resize.window="positionPopover()"
        @scroll.window="positionPopover()"
        x-effect="if(sidebarOpen) popoverOpen=false"
    >
        <button
            type="button"
            @click.stop="toggle($event)"
            class="{{ $linkBase }} {{ $isInventario
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                text-white font-semibold
                drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
                border-blue-400/70
                shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20
                    text-slate-700 dark:text-slate-400
                    hover:bg-white/20 dark:hover:bg-slate-900/30
                    hover:text-slate-900 dark:hover:text-white' }}"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'"
            title="Inventario"
        >
            <div class="flex items-center">
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>Inventario</span>
            </div>

            <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
                fill="currentColor" viewBox="0 0 20 20"
                x-show="sidebarOpen" x-transition
                :class="isOpen() ? 'rotate-180' : ''"
                style="transition: transform .2s ease;">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="isOpen()" x-transition class="mt-2 space-y-1 pl-2">
            <div class="rounded-2xl border
                        bg-white/60 dark:bg-slate-950/40
                        border-slate-200/70 dark:border-white/10
                        backdrop-blur-xl overflow-x-hidden">
                @foreach($inventarioItems as $it)
                    <a href="{{ $it['href'] }}"
                    class="block px-4 py-2.5 text-[0.80rem]
                            text-slate-700 dark:text-slate-200
                            hover:bg-slate-100/90 dark:hover:bg-slate-800/70
                            hover:text-slate-900 dark:hover:text-white
                            transition-colors duration-150">
                        {{ $it['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="popoverOpen && !sidebarOpen"
                x-transition.opacity.duration.150ms
                @click.outside="popoverOpen=false"
                class="fixed z-[999999] pointer-events-auto"
                :style="popoverStyle"
            >
                <div class="rounded-2xl border
                            bg-white/90 dark:bg-slate-900/95
                            border-slate-200/70 dark:border-slate-700/70
                            shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                            backdrop-blur-xl overflow-hidden">
                    @foreach($inventarioItems as $it)
                        <a href="{{ $it['href'] }}"
                        class="block px-4 py-3 text-[0.80rem]
                                text-slate-700 dark:text-slate-200
                                hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                                hover:text-slate-900 dark:hover:text-white
                                transition-colors duration-150">
                            {{ $it['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </template>
    </div>
    @endif

    {{-- ===================== USUARIOS (ADMINISTRACIÓN) ===================== --}}
    @if($puedeUsuarios)
    <div class="w-full mt-3 px-3"
        x-data="{
            id: 'usuarios',
            popoverOpen: false,
            popoverStyle: '',
            triggerEl: null,

            isOpen(){ return sidebarOpen && activeMenu === this.id; },

            toggle(e){
                if (sidebarOpen) { 
                    setMenu(this.id); 
                    return; 
                }

                if (this.popoverOpen) {
                    this.popoverOpen = false;
                    return;
                }

                window.dispatchEvent(new CustomEvent('tb-close-popovers'));
                this.triggerEl = e.currentTarget;
                this.popoverOpen = true;
                this.positionPopover();
            },

            positionPopover(){
                this.$nextTick(() => {
                    if (!this.triggerEl) return;
                    const r = this.triggerEl.getBoundingClientRect();
                    const width = 260, gap = 12;
                    let top  = r.top;
                    let left = r.right + gap;
                    const estimatedHeight = 120;
                    const maxTop = window.innerHeight - 12;
                    if (top + estimatedHeight > maxTop) top = Math.max(12, maxTop - estimatedHeight);
                    this.popoverStyle = `top:${top}px; left:${left}px; width:${width}px;`;
                });
            },

            closePopover(){ this.popoverOpen = false; },
            closeAll(){ this.popoverOpen = false; }
        }"
        @tb-close-popovers.window="closePopover()"
        @keydown.escape.window="closeAll()"
        @resize.window="positionPopover()"
        @scroll.window="positionPopover()"
        x-effect="if(sidebarOpen) popoverOpen=false"
    >
        <button
            type="button"
            @click.stop="toggle($event)"
            class="{{ $linkBase }} {{ $isUsuarios
                ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
                text-white font-semibold
                drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
                border-blue-400/70
                shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
                : 'bg-white/10 dark:bg-slate-900/20
                    text-slate-700 dark:text-slate-400
                    hover:bg-white/20 dark:hover:bg-slate-900/30
                    hover:text-slate-900 dark:hover:text-white' }}"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'"
            title="Usuarios"
        >
            <div class="flex items-center">
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>Usuarios</span>
            </div>

            <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
                fill="currentColor" viewBox="0 0 20 20"
                x-show="sidebarOpen" x-transition
                :class="isOpen() ? 'rotate-180' : ''"
                style="transition: transform .2s ease;">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        @php
        $usuariosItems = [
            [
                'label' => 'Gestión de Usuarios',
                'href'  => route('users.index'),
            ],
        ];
        if ($puedeUsuariosCrear) {
            $usuariosItems[] = [
                'label' => 'Crear Usuario',
                'href'  => route('register'),
            ];
        }
        @endphp

        <div x-show="isOpen()" x-transition class="mt-2 space-y-1 pl-2">
            <div class="rounded-2xl border
                        bg-white/60 dark:bg-slate-950/40
                        border-slate-200/70 dark:border-white/10
                        backdrop-blur-xl overflow-x-hidden">
                @foreach($usuariosItems as $it)
                    <a href="{{ $it['href'] }}"
                    class="block px-4 py-2.5 text-[0.80rem]
                            text-slate-700 dark:text-slate-200
                            hover:bg-slate-100/90 dark:hover:bg-slate-800/70
                            hover:text-slate-900 dark:hover:text-white
                            transition-colors duration-150">
                        {{ $it['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="popoverOpen && !sidebarOpen"
                x-transition.opacity.duration.150ms
                @click.outside="popoverOpen=false"
                class="fixed z-[999999] pointer-events-auto"
                :style="popoverStyle"
            >
                <div class="rounded-2xl border
                            bg-white/90 dark:bg-slate-900/95
                            border-slate-200/70 dark:border-slate-700/70
                            shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                            backdrop-blur-xl overflow-hidden">
                    @foreach($usuariosItems as $it)
                        <a href="{{ $it['href'] }}"
                        class="block px-4 py-3 text-[0.80rem]
                                text-slate-700 dark:text-slate-200
                                hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                                hover:text-slate-900 dark:hover:text-white
                                transition-colors duration-150">
                            {{ $it['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </template>
    </div>
    @endif

</nav>


        {{-- FOOTER / PERFIL + MODO OSCURO (teleport) --}}
        <div
            class="border-t border-slate-200/90 dark:border-slate-800/80 mt-2 pt-2"
            x-data="{ profileMenuOpen: false }"
        >
            <button
                class="group flex items-center w-full px-4 py-3 text-sm font-medium
                    text-slate-700 dark:text-slate-300
                    rounded-2xl
                    bg-white/60 dark:bg-slate-900/60
                    hover:bg-white/85 dark:hover:bg-slate-900/80
                    hover:text-slate-900 dark:hover:text-white
                    hover:shadow-[0_12px_30px_rgba(15,23,42,0.55)]
                    transition-all duration-200"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                @click="profileMenuOpen = !profileMenuOpen"
            >
                <div class="flex items-center justify-center w-9 h-9 rounded-2xl overflow-hidden bg-slate-300/20 backdrop-blur">
                    @if(Auth::user()->foto_perfil ?? false)
                        <img
                            src="{{ asset('storage/' . Auth::user()->foto_perfil) }}"
                            alt="Foto de perfil"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <span class="text-white font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->nombre,0,1)) }}
                        </span>
                    @endif
                </div>

                <div class="ml-3 text-left space-y-0.5" x-show="sidebarOpen" x-transition>
                    <div class="text-base font-semibold text-slate-900 dark:text-slate-50">
                        {{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}
                    </div>
                    <div class="text-[0.7rem] text-slate-500 dark:text-slate-400 truncate max-w-[10rem]">
                        {{ Auth::user()->email }}
                    </div>
                </div>

                <div class="ms-auto" x-show="sidebarOpen" x-transition>
                    <svg class="w-5 h-5 ml-2
                                text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                                transition-colors duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h.01M12 12h.01M19 12h.01" />
                    </svg>
                </div>
            </button>

            {{-- MENÚ TELEPORTADO --}}
            <template x-teleport="body">
                <div
                    x-show="profileMenuOpen"
                    x-transition
                    @click.outside="profileMenuOpen = false"
                    x-on:keydown.escape.window="profileMenuOpen = false"
                    class="fixed z-[9999] transition-all duration-300 left-0"
                    :style="'bottom: 4.5rem;'"
                >
                    <div class="
                        w-64 rounded-2xl
                        bg-white/70 dark:bg-slate-900/90
                        border border-slate-300/60 dark:border-slate-700/60
                        backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.55)]
                        text-slate-800 dark:text-slate-100
                        overflow-hidden
                    ">
                        {{-- MODO CLARO / OSCURO --}}
                        <div class="px-4 py-3 border-b border-slate-300/60 dark:border-slate-700/70">
                            <button
                                @click="darkMode = !darkMode"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm
                                    rounded-lg
                                    bg-white/60 dark:bg-slate-800/80
                                    hover:bg-white/80 dark:hover:bg-slate-700/80
                                    text-slate-700 dark:text-slate-100
                                    transition-colors duration-150"
                            >
                                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>

                                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>

                                <span x-show="!darkMode">Modo oscuro</span>
                                <span x-show="darkMode">Modo claro</span>
                            </button>
                        </div>

                        {{-- PROFILE / LOGOUT --}}
                        <div class="py-1 text-sm">
                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2
                                      text-slate-700 dark:text-slate-200
                                      hover:bg-slate-200/70 dark:hover:bg-slate-800/80
                                      transition-colors duration-150">
                                Perfil
                            </a>
                            @if($puedeAvisos)
                            <a href="{{ route('avisos.index') }}"
                               class="block px-4 py-2
                                      text-slate-700 dark:text-slate-200
                                      hover:bg-slate-200/70 dark:hover:bg-slate-800/80
                                      transition-colors duration-150">
                                Anuncios
                            </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-4 py-2
                                               text-slate-700 dark:text-slate-200
                                               hover:bg-slate-200/70 dark:hover:bg-slate-800/80
                                               transition-colors duration-150">
                                    Salir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

        </div>

    </div>
</div>
