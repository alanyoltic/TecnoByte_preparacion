<div
    class="fixed inset-y-0 left-0 z-30 h-screen 
           /* Fondo modo claro */
           bg-slate-50/80 
           bg-gradient-to-b from-slate-50/90 via-slate-100/95 to-slate-200/90
           /* Fondo modo oscuro */
           dark:bg-slate-900/70 
           dark:bg-gradient-to-b dark:from-slate-900/80 dark:via-slate-950/90 dark:to-slate-900/95
           /* Glass global */
           backdrop-blur-2xl
           /* Bordes */
           border-r border-slate-200/80 dark:border-slate-800/80
           /* Sombra */
           shadow-[0_0_30px_rgba(15,23,42,0.35)]
           transition-all duration-500 ease-[cubic-bezier(0.22,0.61,0.36,1)]"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
>
    <div class="flex flex-col h-full">

        {{-- HEADER / BRAND --}}
        <div 
            class="flex items-center h-16 px-4"
            :class="sidebarOpen ? 'justify-between' : 'justify-center'"
        >
            {{-- Logo / Nombre --}}
            <a href="{{ route('dashboard') }}" 
               class="text-slate-900 dark:text-slate-50 text-xl font-semibold tracking-tight 
                      flex items-center gap-2"
               x-show="sidebarOpen"
               x-transition.opacity.duration.300ms
            >
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-2xl
                           bg-gradient-to-tr from-indigo-500 via-indigo-400 to-blue-500
                           shadow-[0_0_20px_rgba(79,70,229,0.7)] text-sm font-bold text-white">
                    TB
                </span>
                <span class="leading-none">TecnoByte</span>
            </a>

            {{-- Botón hamburguesa --}}
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="p-2 rounded-xl 
                       text-slate-500 dark:text-slate-400 
                       hover:text-slate-900 dark:hover:text-slate-50 
                       hover:bg-slate-200/70 dark:hover:bg-white/10 
                       focus:outline-none
                       transition-all duration-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- NAV --}}
        <nav class="mt-4 flex-1 space-y-1">

            @php
                $linkBase = "group relative flex items-center rounded-xl 
                             px-3 py-2 mx-2 text-sm font-medium
                             text-slate-700 dark:text-slate-300
                             transition-all duration-200 
                             hover:text-slate-900 dark:hover:text-white
                             hover:bg-slate-200/70 dark:hover:bg-white/5";
                $iconBase = "w-6 h-6 flex-shrink-0 
                             text-slate-400 dark:text-slate-400 
                             group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                             transition-colors duration-200";
                $labelBase = "ml-3 whitespace-nowrap";
            @endphp

            {{-- DASHBOARD --}}
            @php $isDashboard = request()->routeIs('dashboard'); @endphp
            <a 
                href="{{ route('dashboard') }}"
                title="Dashboard"
                :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                class="{{ $linkBase }} {{ $isDashboard ? 'bg-slate-200/80 dark:bg-white/10 border-l-4 border-indigo-500/80' : 'border-l-4 border-transparent' }}"
            >
                <svg class="{{ $iconBase }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 15h.01M12 9h.01M9 15h.01M12 15h.01" />
                </svg>
                <span class="{{ $labelBase }}" x-show="sidebarOpen" x-transition>
                    Dashboard
                </span>
            </a>

            {{-- USUARIOS (CEO / ADMIN) --}}
            @if(in_array(auth()->user()->role?->slug, ['ceo', 'admin']))
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button 
                            class="{{ $linkBase }} w-full mt-1 
                                   flex items-center
                                   border-l-4 border-transparent
                                   @if(request()->routeIs('users.*') || request()->routeIs('register')) 
                                        bg-slate-200/80 dark:bg-white/10 border-indigo-500/80 
                                   @endif"
                            :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                            title="Administrar Usuarios"
                        >
                            <svg class="{{ $iconBase }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
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
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('register')">Agregar Usuario</x-dropdown-link>
                        <x-dropdown-link :href="route('users.index')">Administrar Usuarios</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            @endif

        </nav>

        {{-- FOOTER / PERFIL + MODO OSCURO --}}
        <div class="border-t border-slate-200/90 dark:border-slate-800/80 mt-2 pt-2">
            <x-dropdown align="top" width="64">
                <x-slot name="trigger">
                    <button 
                        class="group flex items-center w-full px-4 py-3 text-xs font-medium 
                               text-slate-700 dark:text-slate-300 
                               rounded-2xl 
                               hover:bg-slate-200/80 dark:hover:bg-white/5
                               hover:text-slate-900 dark:hover:text-white
                               transition-all duration-200"
                        :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                    >
                        {{-- Avatar --}}
                        <div
                            class="flex items-center justify-center w-9 h-9 rounded-2xl
                                   bg-gradient-to-tr from-indigo-500 via-blue-500 to-sky-400
                                   shadow-[0_0_20px_rgba(59,130,246,0.65)]
                                   text-sm font-semibold text-white"
                        >
                            {{ strtoupper(substr(Auth::user()->nombre,0,1)) }}
                        </div>

                        {{-- Nombre + correo --}}
                        <div class="ml-3 text-left space-y-0.5" x-show="sidebarOpen" x-transition>
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                {{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}
                            </div>
                            <div class="text-[0.7rem] text-slate-500 dark:text-slate-400 truncate max-w-[10rem]">
                                {{ Auth::user()->email }}
                            </div>
                        </div>

                        {{-- Icono opciones --}}
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
                </x-slot>

                <x-slot name="content">
                    {{-- Botón modo oscuro --}}
                    <div class="px-4 py-2">
                        <button 
                            @click="darkMode = !darkMode" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm
                                   text-slate-800 dark:text-slate-200 rounded-lg
                                   bg-slate-100/80 dark:bg-slate-800/70
                                   hover:bg-slate-200/90 dark:hover:bg-slate-700/80
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

                    <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>

                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" 
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

    </div>
</div>
