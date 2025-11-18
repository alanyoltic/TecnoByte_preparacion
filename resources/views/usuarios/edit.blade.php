<x-app-layout>

    {{-- HEADER PREMIUM --}}
    <x-slot name="header">
        <div class="-mx-4 sm:-mx-6 lg:-mx-8">
            <div
                class="px-4 sm:px-6 lg:px-8 py-3
                       bg-gradient-to-r 
                           from-slate-50 via-slate-100 to-slate-50
                       dark:from-slate-900 dark:via-slate-950 dark:to-slate-900
                       backdrop-blur-xl
                       border-b border-slate-200/80 dark:border-slate-800/80
                       shadow-md shadow-slate-900/50"
            >
                <div class="flex items-center justify-between gap-4">

                    <div class="flex items-start gap-3">
                        {{-- Iconito --}}
                        <div
                            class="mt-1 inline-flex items-center justify-center w-9 h-9 rounded-2xl
                                   bg-gradient-to-tr from-indigo-500 via-blue-500 to-sky-400
                                   text-white shadow-[0_0_18px_rgba(59,130,246,0.6)]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 7l5-5 5 5M5 17l5 5 5-5" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Editar Usuario
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Actualiza la información del usuario seleccionado.
                            </p>
                        </div>
                    </div>

                    <span
                        class="hidden sm:inline-flex items-center px-3 py-1 rounded-full
                               text-[0.7rem] font-medium tracking-wide
                               bg-slate-100/80 dark:bg-slate-800/80
                               text-slate-600 dark:text-slate-200
                               border border-slate-200/80 dark:border-slate-700/80"
                    >
                        ID: {{ $usuario->id }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- CONTENIDO --}}
    <div class="py-10 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300
                dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 min-h-screen">

        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div
                class="max-w-4xl mx-auto
                       rounded-3xl
                       bg-white/80 dark:bg-slate-900/80
                       backdrop-blur-2xl
                       border border-white/50 dark:border-slate-800/80
                       shadow-2xl shadow-slate-900/60
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
                            Modifica solo la información necesaria. La contraseña es opcional.
                        </p>
                    </div>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium
                                 bg-emerald-500/10 text-emerald-400 border border-emerald-400/40">
                        Usuario activo
                    </span>
                </div>

                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('users.update', $usuario) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

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
                                    :value="old('nombre', $usuario->nombre)"
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
                                    :value="old('segundo_nombre', $usuario->segundo_nombre)"
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
                                    :value="old('apellido_paterno', $usuario->apellido_paterno)"
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
                                    :value="old('apellido_materno', $usuario->apellido_materno)"
                                />
                                <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
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
                                    :value="old('email', $usuario->email)"
                                    required
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="password" value="Nueva Contraseña (Opcional)" />
                                    <x-text-input
                                        id="password"
                                        class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                    />
                                    <p class="mt-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        Déjalo en blanco si no deseas cambiar la contraseña.
                                    </p>
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                                    <x-text-input
                                        id="password_confirmation"
                                        class="block mt-1 w-full"
                                        type="password"
                                        name="password_confirmation"
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
                                        <option
                                            value="{{ $role->id }}"
                                            {{ old('role_id', $usuario->role_id) == $role->id ? 'selected' : '' }}
                                        >
                                            {{ $role->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                            </div>

                            <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                <p>
                                    El rol controla los permisos del usuario. Puedes modificarlo en cualquier momento.
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

                        <x-primary-button class="ms-4">
                            Guardar cambios
                        </x-primary-button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</x-app-layout>
