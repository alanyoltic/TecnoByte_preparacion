<div
    class="fixed inset-y-0 left-0 z-50 h-screen
           transition-all duration-300 ease-in-out

           bg-gradient-to-b
           from-slate-100/90 via-slate-100/80 to-slate-200/70   {{-- 🌞 MODO CLARO --}}
           dark:from-slate-950/95 dark:via-slate-950/90 dark:to-slate-900/80  {{-- 🌙 MODO OSCURO --}}

           backdrop-blur-xl
           overflow-hidden"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
>



    {{-- Glows del sidebar para que combine con el dashboard --}}
    <div class="pointer-events-none absolute -top-16 -right-10 w-40 h-40
                bg-sky-500/35 dark:bg-sky-500/45 blur-3xl opacity-70"></div>
    <div class="pointer-events-none absolute bottom-0 -left-10 w-32 h-32
                bg-indigo-500/25 dark:bg-indigo-500/35 blur-3xl opacity-60"></div>

    <div class="flex flex-col h-full relative z-10">

        

{{-- HEADER / BRAND --}}
<div class="flex items-center h-16 px-3">
    {{-- Botón hamburguesa: SIEMPRE a la izquierda, siempre en el mismo lugar --}}
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

    {{-- LOGO TECNOBYTE: sale a la DERECHA del botón cuando el sidebar está abierto --}}
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



<nav class="mt-3 flex-1 space-y-1">
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

    $isDashboard  = request()->routeIs('dashboard');
    $isEquipos    = request()->routeIs('equipos.*');
    $isInventario = request()->routeIs('inventario.*');
@endphp


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

{{-- ===================== EQUIPOS (DROPDOWN) ===================== --}}
@if(auth()->check() && in_array(auth()->user()->role?->slug, ['tecnico', 'admin', 'ceo']))
    <div class="w-full mt-3 px-3">
        <x-dropdown
            align="right"
            width="64"
            contentClasses="py-2 bg-white/90 dark:bg-slate-900/95
                            border border-slate-200/70 dark:border-slate-700/70
                            rounded-2xl shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                            backdrop-blur-xl"
        >
<x-slot name="trigger">
    <button
        type="button"
        class="{{ $linkBase }} {{ $isEquipos
            ? 'bg-gradient-to-r from-[#1E3A8A] via-[#3B82F6] to-[#2563EB]
               text-white font-semibold
               drop-shadow-[0_0_6px_rgba(99,102,241,0.65)]
               border-blue-400/70
               shadow-[0_14px_35px_rgba(37,99,235,0.85)]'
            : 'bg-white/10 dark:bg-slate-900/20
                text-slate-700 dark:text-slate-400
                hover:bg-white/20 dark:hover:bg-slate-900/30
                hover:text-slate-900 dark:hover:text-white'}}"
        :class="sidebarOpen ? 'justify-between' : 'justify-center'"
        title="Gestión de Equipos"
    >
        <div class="flex items-center">
            <div class="flex items-center justify-center w-7 h-7">
<svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white"
     fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
</svg>

            </div>

            <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>
                Equipos
            </span>
        </div>

        <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
             fill="currentColor" viewBox="0 0 20 20" x-show="sidebarOpen" x-transition>
            <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
        </svg>
    </button>
</x-slot>


            <x-slot name="content">
                <x-dropdown-link
                    :href="route('equipos.create')"
                    class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                           text-slate-700 dark:text-slate-200
                           hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                           hover:text-slate-900 dark:hover:text-white
                           transition-colors duration-150"
                >
                    Registrar Entrada
                </x-dropdown-link>


                <x-dropdown-link
                    href="#"
                    class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                           text-slate-700 dark:text-slate-200
                           hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                           hover:text-slate-900 dark:hover:text-white
                           transition-colors duración-150"
                >
                    Garantía Proveedor
                </x-dropdown-link>

                <x-dropdown-link
                    :href="route('equipos.piezas-pendientes')"
                    class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                           text-slate-700 dark:text-slate-200
                           hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                           hover:text-slate-900 dark:hover:text-white
                           transition-colors duration-150"
                >
                    Pendiente de piezas
                </x-dropdown-link>
            </x-slot>
        </x-dropdown>
    </div>


{{-- ===================== INVENTARIO ===================== --}}
@php
    $esAdminCeo = in_array(optional(auth()->user()->role)->slug, ['admin', 'ceo']);
@endphp

<x-dropdown
    align="right"
    width="64"
    contentClasses="py-2 bg-white/90 dark:bg-slate-900/95
                    border border-slate-200/70 dark:border-slate-700/70
                    rounded-2xl shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                    backdrop-blur-xl"
>
    <x-slot name="trigger">
        <div class="w-full mt-2 px-3">
            <button
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
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                title="Inventario"
            >
                <div class="flex items-center justify-center w-7 h-7">
                    <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7l9-4 9 4-9 4-9-4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7v10l9 4 9-4V7" />
                    </svg>
                </div>

                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>
                    Inventario
                </span>

                <svg class="w-4 h-4 ml-auto
                            text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                            transition-colors duration-200"
                    fill="currentColor" viewBox="0 0 20 20"
                    x-show="sidebarOpen" x-transition>
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </x-slot>

    <x-slot name="content">
        {{-- Vista normal: inventario listo (para todos los que tengan permiso a esa ruta) --}}
        <x-dropdown-link
            :href="route('inventario.listo')"
            class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                   text-slate-700 dark:text-slate-200
                   hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                   hover:text-slate-900 dark:hover:text-white
                   transition-colors duration-150"
        >
            Inventario listo
        </x-dropdown-link>

        {{-- Panel solo para CEO / Admin --}}
        @if($esAdminCeo)
            <x-dropdown-link
                :href="route('inventario.admin')"
                class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                       text-slate-700 dark:text-slate-200
                       hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                       hover:text-slate-900 dark:hover:text-white
                       transition-colors duration-150"
            >
                Editar inventario
            </x-dropdown-link>
        @endif
    </x-slot>
</x-dropdown>


    @endif

    {{-- ===================== USUARIOS ===================== --}}
{{-- ===================== USUARIOS ===================== --}}
@if(in_array(auth()->user()->role?->slug, ['ceo', 'admin']))
    @php
        $isUsuarios = request()->routeIs('users.*') || request()->routeIs('register');
    @endphp

    <x-dropdown
        align="right"
        width="64"
        contentClasses="py-2 bg-white/90 dark:bg-slate-900/95
                        border border-slate-200/70 dark:border-slate-700/70
                        rounded-2xl shadow-[0_18px_45px_rgba(15,23,42,0.65)]
                        backdrop-blur-xl"
    >
        <x-slot name="trigger">
    <div class="w-full mt-2 px-3">
        <button
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
            :class="sidebarOpen ? 'justify-start' : 'justify-center'"
            title="Administrar Usuarios"
        >
            <div class="flex items-center justify-center w-7 h-7">
                <svg class="{{ $iconBase }} group-[.bg-gradient-to-r]:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>

            <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>
                Usuarios
            </span>

            <svg class="w-4 h-4 ml-auto
                        text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                        transition-colors duration-200"
                 fill="currentColor" viewBox="0 0 20 20"
                 x-show="sidebarOpen" x-transition>
                <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</x-slot>


        <x-slot name="content">
            <x-dropdown-link
                :href="route('register')"
                class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                       text-slate-700 dark:text-slate-200
                       hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                       hover:text-slate-900 dark:hover:text-white
                       transition-colors duration-150"
            >
                Agregar Usuario
            </x-dropdown-link>

            <x-dropdown-link
                :href="route('users.index')"
                class="flex items-center gap-2 px-4 py-2 text-[0.80rem]
                       text-slate-700 dark:text-slate-200
                       hover:bg-slate-100/90 dark:hover:bg-slate-800/80
                       hover:text-slate-900 dark:hover:text-white
                       transition-colors duration-150"
            >
                Administrar Usuarios
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif

</nav>



{{-- FOOTER / PERFIL + MODO OSCURO (MENU TELEPORTADO FUERA DEL SIDEBAR) --}}
            <div
                class="border-t border-slate-200/90 dark:border-slate-800/80 mt-2 pt-2"
                x-data="{ profileMenuOpen: false }"
            >
                {{-- BOTÓN PERFIL --}}
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
                    {{-- FOTO DEL USUARIO O INICIAL --}}
                    <div class="flex items-center justify-center w-9 h-9 rounded-2xl overflow-hidden bg-slate-300/20 backdrop-blur">
                        @if(Auth::user()->foto_perfil ?? false)
                            {{-- Si hay foto --}}
                            <img 
                                src="{{ asset('storage/' . Auth::user()->foto_perfil) }}" 
                                alt="Foto de perfil"
                                class="w-full h-full object-cover"
                            >
                        @else
                            {{-- Si NO hay foto, mostrar inicial --}}
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

                {{-- MENÚ TELEPORTADO FUERA DEL SIDEBAR --}}
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
                            bg-white/70 dark:bg-slate-900/90   {{-- 🌞 modo claro / 🌙 modo oscuro --}}
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
                                        transition-colors duration-150"
                                >
                                    Perfil
                                </a>

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
