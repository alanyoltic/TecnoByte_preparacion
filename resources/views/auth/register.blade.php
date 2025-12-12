<x-app-layout>

    {{-- FONDO ESTILO LOGIN / DASHBOARD --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-100 to-slate-200
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >

        {{-- Luces estilo glow con posiciones aleatorias --}}
        @php
            // Azul superior izq
            $glow1Top  = rand(-420, -260);
            $glow1Left = rand(-340, -120);

            // Azul inferior der
            $glow2Bottom = rand(-420, -260);
            $glow2Right  = rand(-340, -120);

            // Naranja central
            $glow3Bottom      = rand(-360, -220);
            $glow3LeftPercent = rand(20, 80);
        @endphp

        <div class="pointer-events-none absolute inset-0">
            {{-- Glow azul grande superior izquierdo --}}
            <div
                class="absolute w-[1100px] h-[1100px]
                       bg-[#1E3A8A] rounded-full blur-[240px]
                       opacity-70 md:opacity-90 mix-blend-screen"
                style="top: {{ $glow1Top }}px; left: {{ $glow1Left }}px;"
            ></div>

            {{-- Glow azul grande inferior derecho --}}
            <div
                class="absolute w-[1000px] h-[1000px]
                       bg-[#0F172A] rounded-full blur-[240px]
                       opacity-70 md:opacity-95 mix-blend-screen"
                style="bottom: {{ $glow2Bottom }}px; right: {{ $glow2Right }}px;"
            ></div>

            {{-- Glow naranja suave central --}}
            <div
                class="absolute w-[850px] h-[850px]
                       bg-[#FF9521]/40 md:bg-[#FF9521]/50
                       rounded-full blur-[260px]
                       opacity-80 md:opacity-90 mix-blend-screen"
                style="bottom: {{ $glow3Bottom }}px; left: {{ $glow3LeftPercent }}%;"
            ></div>
        </div>

        {{-- Capa glass suave global --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO: HEADER + FORMULARIO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6">
            

                {{-- HEADER GLASS DE LA VISTA --}}
                <div
                    class="relative overflow-hidden
                           rounded-3xl
                           bg-white/80 dark:bg-slate-950/70
                           border border-slate-200/80 dark:border-white/10
                           shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           px-5 sm:px-8 lg:px-10 py-4 sm:py-5"
                >
                    <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">

                        <div class="flex items-start gap-3">
                            {{-- Icono redondo --}}


                            <div>
                                <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                    Registrar Nuevo Usuario
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                    Completa la información para agregar un nuevo colaborador al sistema.
                                </p>
                            </div>
                        </div>

                        {{-- Etiqueta de contexto --}}
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full
                                   text-[0.7rem] font-medium tracking-wide
                                   bg-indigo-500/10 text-indigo-600
                                   dark:bg-indigo-400/15 dark:text-indigo-200
                                   border border-indigo-500/30"
                        >
                            Módulo: Administración de usuarios
                        </span>
                    </div>
                </div>

            <div class="max-w-5xl mx-auto space-y-6">

                {{-- CARD PRINCIPAL DEL FORMULARIO --}}
                <div
                    class="rounded-3xl
                           bg-white/85 dark:bg-slate-950/75
                           border border-slate-200/80 dark:border-white/10
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           shadow-xl shadow-slate-900/15 dark:shadow-2xl dark:shadow-slate-950/70
                           px-5 sm:px-8 py-6 sm:py-8
                           space-y-6"
                >

                    {{-- Encabezado interno --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50">
                                Datos del usuario
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Los campos marcados con <span class="text-red-500">*</span> son obligatorios.
                            </p>
                        </div>

                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium
                                   bg-slate-100/80 dark:bg-slate-800/80
                                   text-slate-600 dark:text-slate-300
                                   border border-slate-200/80 dark:border-slate-700/80">
                            Nuevo registro
                        </span>
                    </div>

                    {{-- FORMULARIO --}}
                    <form method="POST" action="{{ route('register') }}" class="space-y-8" enctype="multipart/form-data">

                        @csrf

                        {{-- SECCIÓN: Datos personales --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Información personal
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="nombre" value="Nombre *" />
                                    <x-text-input
                                        id="nombre"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="nombre"
                                        :value="old('nombre')"
                                        required
                                        autofocus
                                    />
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="segundo_nombre" value="Segundo Nombre (Opcional)" />
                                    <x-text-input
                                        id="segundo_nombre"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="segundo_nombre"
                                        :value="old('segundo_nombre')"
                                    />
                                    <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_paterno" value="Apellido Paterno *" />
                                    <x-text-input
                                        id="apellido_paterno"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="apellido_paterno"
                                        :value="old('apellido_paterno')"
                                        required
                                    />
                                    <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_materno" value="Apellido Materno (Opcional)" />
                                    <x-text-input
                                        id="apellido_materno"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="apellido_materno"
                                        :value="old('apellido_materno')"
                                    />
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
                                {{-- Preview inicial: como es usuario nuevo, normalmente no habrá foto --}}
                                <div>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl
                                            bg-slate-200/70 dark:bg-slate-800/70
                                            border border-slate-200/80 dark:border-slate-700/80
                                            flex items-center justify-center
                                            text-[0.7rem] text-slate-500 dark:text-slate-400"
                                    >
                                        Sin foto
                                    </div>
                                </div>

                                {{-- Input para subir nueva foto --}}
                                <div class="flex-1 space-y-1">
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-200">
                                        Subir foto de perfil (Opcional)
                                    </label>

                                    <input
                                        type="file"
                                        name="foto_perfil"
                                        accept="image/*"
                                        class="block w-full text-xs sm:text-sm text-slate-700 dark:text-slate-200
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-xl file:border-0
                                            file:text-xs sm:file:text-sm file:font-semibold
                                            file:bg-blue-600 file:text-white
                                            hover:file:bg-blue-700
                                            cursor-pointer
                                            bg-white/70 dark:bg-slate-900/70
                                            border border-slate-200/80 dark:border-slate-700/80
                                            rounded-xl shadow-sm shadow-slate-900/10"
                                    >

                                    <p class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        Formatos permitidos: JPG, PNG. Tamaño máximo: 20 MB.
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
                                <div>
                                    <x-input-label for="email" value="Email *" />
                                    <x-text-input
                                        id="email"
                                        class="block mt-1 w-full"
                                        type="email"
                                        name="email"
                                        :value="old('email')"
                                        required
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="password" value="Contraseña *" />
                                        <x-text-input
                                            id="password"
                                            class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required
                                        />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" value="Confirmar Contraseña *" />
                                        <x-text-input
                                            id="password_confirmation"
                                            class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation"
                                            required
                                        />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        {{-- SECCIÓN: Rol --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Rol y permisos
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="role_id" value="Asignar Rol *" />

                                    <select
                                        id="role_id"
                                        name="role_id"
                                        class="block mt-1 w-full rounded-xl
                                               border border-slate-300/80 dark:border-slate-700/80
                                               bg-white/90 dark:bg-slate-900/80
                                               text-sm text-slate-800 dark:text-slate-100
                                               shadow-sm shadow-slate-900/10
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                        required
                                    >
                                        <option value="">-- Seleccionar un rol --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->nombre }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                                </div>

                                <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                    <p>
                                        El rol define los permisos y accesos del usuario dentro del sistema.
                                        Puedes cambiarlo después desde el módulo de administración de usuarios.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- BOTONES --}}
                        <div class="flex items-center justify-between pt-2">
                            <a
                                href="{{ route('users.index') }}"
                                class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300
                                       inline-flex items-center gap-1 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7" />
                                </svg>
                                Volver al listado
                            </a>

                            {{-- Botón principal (azul estándar) --}}
                            <x-primary-button class="ms-4">
                                Crear Usuario
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
            
            </div>
        </div>
    </div>

</x-app-layout>
