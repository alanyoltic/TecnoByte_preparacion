<x-app-layout>


    @php
        // Cálculo SOLO para la vista (no afecta al backend)
        $esAdminOCeo = in_array(optional($user->role)->slug, ['admin', 'ceo']);
    @endphp

    {{-- FONDO ESTILO TECNOBYTE (IGUAL QUE EDITAR USUARIO) --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-200 to-slate-300
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >
        {{-- LUCES DE FONDO --}}
        @php
            $g1Top   = rand(-440, -260);
            $g1Left  = rand(-360, -140);
            $g2Bot   = rand(-420, -260);
            $g2Right = rand(-360, -140);
            $g3Bot   = rand(-360, -220);
            $g3Left  = rand(25, 75);
        @endphp

        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute w-[1100px] h-[1100px] bg-[#1E3A8A] rounded-full blur-[240px] opacity-70 md:opacity-90 mix-blend-screen"
                 style="top: {{ $g1Top }}px; left: {{ $g1Left }}px;"></div>
            <div class="absolute w-[950px] h-[950px] bg-[#0F172A] rounded-full blur-[230px] opacity-60 md:opacity-80 mix-blend-screen"
                 style="bottom: {{ $g2Bot }}px; right: {{ $g2Right }}px;"></div>
            <div class="absolute w-[800px] h-[800px] bg-[#FF9521]/45 rounded-full blur-[250px] opacity-80 md:opacity-90 mix-blend-screen"
                 style="bottom: {{ $g3Bot }}px; left: {{ $g3Left }}%;"></div>
        </div>

        {{-- CAPA GLASS GLOBAL --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6">

                {{-- HEADER GLASS --}}
                <div
                    class="relative overflow-hidden
                           rounded-3xl
                           bg-white/80 dark:bg-slate-950/70
                           border border-slate-200/80 dark:border-white/10
                           shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           px-5 sm:px-8 lg:px-10 py-4 sm:py-5"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                    Editar mi perfil
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    Actualiza tu información personal y credenciales de acceso.
                                </p>
                            </div>
                        </div>

                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full
                                     text-[0.7rem] font-medium tracking-wide
                                     bg-slate-100/80 dark:bg-slate-800/80
                                     text-slate-600 dark:text-slate-200
                                     border border-slate-200/80 dark:border-slate-700/80">
                            ID: {{ $user->id }}
                        </span>
                    </div>
                </div>

                {{-- TARJETA DEL FORMULARIO --}}
                <div
                    class="max-w-4xl mx-auto
                           rounded-3xl
                           bg-white/85 dark:bg-slate-950/80
                           border border-slate-200/80 dark:border-white/10
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           shadow-xl shadow-slate-900/15
                           dark:shadow-2xl dark:shadow-slate-950/70
                           px-5 sm:px-8 py-6 sm:py-8
                           space-y-6"
                >
                    {{-- Encabezado interno --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50">
                                Datos del perfil
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Modifica solo la información necesaria. La contraseña es opcional.
                            </p>
                        </div>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium
                                     bg-emerald-500/10 text-emerald-400 border border-emerald-400/40">
                            Sesión activa
                        </span>
                    </div>

                    {{-- FORMULARIO --}}
                    <form method="POST"
                          action="{{ route('profile.update') }}"
                          class="space-y-8"
                          enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')


                        {{-- SECCIÓN: Datos personales --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Información personal
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- NOMBRE --}}
                                <div>
                                    <label for="nombre"
                                        class="block text-xs font-medium text-slate-200">
                                        Nombre *
                                    </label>

                                    <input
                                        id="nombre"
                                        name="nombre"
                                        type="text"
                                        value="{{ old('nombre', $user->nombre) }}"
                                        class="mt-1 block w-full rounded-2xl border border-slate-700
                                            bg-slate-900/80 text-slate-100 text-sm
                                            shadow-sm focus:outline-none
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >

                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>

                                {{-- SEGUNDO NOMBRE --}}
                                <div>
                                    <label for="segundo_nombre"
                                        class="block text-xs font-medium text-slate-200">
                                        Segundo Nombre (Opcional)
                                    </label>

                                    <input
                                        id="segundo_nombre"
                                        name="segundo_nombre"
                                        type="text"
                                        value="{{ old('segundo_nombre', $user->segundo_nombre) }}"
                                        class="mt-1 block w-full rounded-2xl border border-slate-700
                                            bg-slate-900/80 text-slate-100 text-sm
                                            shadow-sm focus:outline-none
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >

                                    <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                                </div>

                                {{-- APELLIDO PATERNO --}}
                                <div>
                                    <label for="apellido_paterno"
                                        class="block text-xs font-medium text-slate-200">
                                        Apellido Paterno *
                                    </label>

                                    <input
                                        id="apellido_paterno"
                                        name="apellido_paterno"
                                        type="text"
                                        value="{{ old('apellido_paterno', $user->apellido_paterno) }}"
                                        class="mt-1 block w-full rounded-2xl border border-slate-700
                                            bg-slate-900/80 text-slate-100 text-sm
                                            shadow-sm focus:outline-none
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >

                                    <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                                </div>

                                {{-- APELLIDO MATERNO --}}
                                <div>
                                    <label for="apellido_materno"
                                        class="block text-xs font-medium text-slate-200">
                                        Apellido Materno (Opcional)
                                    </label>

                                    <input
                                        id="apellido_materno"
                                        name="apellido_materno"
                                        type="text"
                                        value="{{ old('apellido_materno', $user->apellido_materno) }}"
                                        class="mt-1 block w-full rounded-2xl border border-slate-700
                                            bg-slate-900/80 text-slate-100 text-sm
                                            shadow-sm focus:outline-none
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >

                                    <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                                </div>
                            </div>
                        </div>


                        {{-- SECCIÓN: Foto de perfil --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Foto de perfil
                            </h4>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                {{-- Preview actual --}}
                                <div>
                                    @if($user->foto_perfil)
                                        <img
                                            src="{{ asset('storage/' . $user->foto_perfil) }}"
                                            alt="Foto de perfil"
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover
                                                   border border-slate-200/80 dark:border-slate-700/80
                                                   shadow-sm shadow-slate-900/20"
                                        >
                                    @else
                                        <div
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl
                                                   bg-slate-200/70 dark:bg-slate-800/70
                                                   border border-slate-200/80 dark:border-slate-700/80
                                                   flex items-center justify-center
                                                   text-[0.7rem] text-slate-500 dark:text-slate-400"
                                        >
                                            Sin foto
                                        </div>
                                    @endif
                                </div>

                                {{-- Input para subir nueva foto --}}
                                <div class="flex-1 space-y-1">
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-200">
                                        Subir nueva foto
                                    </label>

                                    <input
                                        type="file"
                                        name="foto_perfil"
                                        accept="image/*"
                                        @if($soloPassword) disabled @endif
                                        class="block w-full text-xs sm:text-sm text-slate-700 dark:text-slate-200
                                               file:mr-4 file:py-2 file:px-4
                                               file:rounded-xl file:border-0
                                               file:text-xs sm:file:text-sm file:font-semibold
                                               file:bg-blue-600 file:text-white
                                               hover:file:bg-blue-700
                                               bg-slate-900/80 dark:bg-slate-950/80
                                               border border-slate-700/80 dark:border-slate-700/80
                                               rounded-xl shadow-sm shadow-slate-900/10
                                               disabled:bg-slate-900/40 disabled:text-slate-500
                                               disabled:border-slate-800 disabled:cursor-not-allowed"
                                    >

                                    <p class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        Formatos permitidos: JPG, PNG. Tamaño máximo: 2 MB.
                                    </p>

                                    @error('foto_perfil')
                                        <p class="text-[0.7rem] text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        {{-- SECCIÓN: Credenciales --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Credenciales de acceso
                            </h4>

                            <div class="space-y-4">
                                {{-- Email --}}
                                <div>
                                    <x-input-label for="email" value="Email *" />

                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email', $user->email) }}"
                                        class="mt-1 block w-full rounded-2xl border border-slate-700
                                            bg-slate-900/80 text-slate-100 text-sm
                                            shadow-sm focus:outline-none
                                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >

                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Nueva contraseña --}}
                                    <div>
                                        <x-input-label for="password" value="Nueva Contraseña (Opcional)" />
                                        <x-text-input
                                            id="password"
                                            name="password"
                                            type="password"
                                            class="block mt-1 w-full rounded-2xl border text-sm shadow-sm transition
                                                   bg-slate-900/80 border-slate-700 text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                        <p class="mt-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                            Déjalo en blanco si no deseas cambiar la contraseña.
                                        </p>
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    {{-- Confirmar contraseña --}}
                                    <div>
                                        <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                                        <x-text-input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            class="block mt-1 w-full rounded-2xl border text-sm shadow-sm transition
                                                   bg-slate-900/80 border-slate-700 text-slate-100
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BOTONES --}}
                        <div class="flex items-center justify-between pt-2">
                            <a href="{{ route('profile.show') }}"
                               class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 inline-flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7" />
                                </svg>
                                Volver a mi perfil
                            </a>

                            <x-primary-button class="ms-4">
                                Guardar cambios
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
