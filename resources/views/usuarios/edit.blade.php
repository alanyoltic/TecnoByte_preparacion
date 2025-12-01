<x-app-layout>

    {{-- 1. ELIMINAMOS <x-slot name="header"> --}}
    {{-- Todo va dentro del wrapper principal para compartir el fondo --}}

    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-200 to-slate-300
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >
        {{-- 2. LUCES DE FONDO (Igual que tenías) --}}
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

        {{-- Capa glass general --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- 3. CONTENIDO (Aquí metemos el Header + el Formulario) --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
        <div class="space-y-6">

            {{-- === AQUÍ ESTÁ EL HEADER MOVIDO === --}}
            {{-- Lo convertí en "tarjeta flotante" (rounded-3xl) para que coincida con tu Dashboard --}}
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
                                Editar Usuario
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Actualiza la información del usuario seleccionado.
                            </p>
                        </div>
                    </div>

                    <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full
                                 text-[0.7rem] font-medium tracking-wide
                                 bg-slate-100/80 dark:bg-slate-800/80
                                 text-slate-600 dark:text-slate-200
                                 border border-slate-200/80 dark:border-slate-700/80">
                        ID: {{ $usuario->id }}
                    </span>
                </div>
            </div>
            {{-- === FIN DEL HEADER === --}}

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
                {{-- Encabezado interno de la tarjeta --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50">
                            Datos del usuario
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Modifica solo la información necesaria. La contraseña es opcional.
                        </p>
                    </div>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium
                                 bg-emerald-500/10 text-emerald-400 border border-emerald-400/40">
                        Usuario activo
                    </span>
                </div>

                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('users.update', $usuario) }}" class="space-y-8" enctype="multipart/form-data">

                    @csrf
                    @method('PATCH')

                    {{-- SECCIÓN: Datos personales --}}
                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Información personal
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nombre" value="Nombre *" />
                                <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $usuario->nombre)" required autofocus />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="segundo_nombre" value="Segundo Nombre (Opcional)" />
                                <x-text-input id="segundo_nombre" class="block mt-1 w-full" type="text" name="segundo_nombre" :value="old('segundo_nombre', $usuario->segundo_nombre)" />
                                <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="apellido_paterno" value="Apellido Paterno *" />
                                <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $usuario->apellido_paterno)" required />
                                <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="apellido_materno" value="Apellido Materno (Opcional)" />
                                <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $usuario->apellido_materno)" />
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
                                @if($usuario->foto_perfil)
                                    <img
                                        src="{{ asset('storage/' . $usuario->foto_perfil) }}"
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
                            <div>
                                <x-input-label for="email" value="Email *" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $usuario->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="password" value="Nueva Contraseña (Opcional)" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                                    <p class="mt-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        Déjalo en blanco si no deseas cambiar la contraseña.
                                    </p>
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
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
                                <select id="role_id" name="role_id" class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/80 text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500" required>
                                    <option value="">-- Seleccionar un rol --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $usuario->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                            </div>

                            <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                <p>El rol controla los permisos del usuario. Puedes modificarlo en cualquier momento.</p>
                            </div>
                        </div>
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('users.index') }}" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 inline-flex items-center gap-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Volver al listado
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