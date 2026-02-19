<x-app-layout>
    @php
        $esAdminOCeo = !($soloPassword ?? true);
    @endphp

    <x-tb-background>
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6">
                <x-topbar
                    title="Editar Perfil"
                    chip="Perfil · Editar"
                    description="Actualiza tu informacion personal y credenciales."
                />

                <div
                    class="max-w-4xl mx-auto
                           rounded-3xl
                           bg-white/85 dark:bg-slate-950/80
                           border border-slate-200/80 dark:border-white/10
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           shadow-xl shadow-slate-900/15 dark:shadow-2xl dark:shadow-slate-950/70
                           px-5 sm:px-8 py-6 sm:py-8
                           space-y-6"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50">
                                Datos del perfil
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Tu rol, departamento y puesto no se pueden editar desde aqui.
                            </p>
                        </div>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-400/40">
                            Sesion activa
                        </span>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-8" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Informacion personal
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="nombre" value="Nombre *" />
                                    <input
                                        id="nombre"
                                        name="nombre"
                                        type="text"
                                        value="{{ old('nombre', $user->nombre) }}"
                                        @if(!$esAdminOCeo) readonly @endif
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 {{ $esAdminOCeo ? 'bg-white/90 dark:bg-slate-900/80' : 'bg-slate-100/80 dark:bg-slate-900/40' }} text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="segundo_nombre" value="Segundo nombre (opcional)" />
                                    <input
                                        id="segundo_nombre"
                                        name="segundo_nombre"
                                        type="text"
                                        value="{{ old('segundo_nombre', $user->segundo_nombre) }}"
                                        @if(!$esAdminOCeo) readonly @endif
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 {{ $esAdminOCeo ? 'bg-white/90 dark:bg-slate-900/80' : 'bg-slate-100/80 dark:bg-slate-900/40' }} text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_paterno" value="Apellido paterno *" />
                                    <input
                                        id="apellido_paterno"
                                        name="apellido_paterno"
                                        type="text"
                                        value="{{ old('apellido_paterno', $user->apellido_paterno) }}"
                                        @if(!$esAdminOCeo) readonly @endif
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 {{ $esAdminOCeo ? 'bg-white/90 dark:bg-slate-900/80' : 'bg-slate-100/80 dark:bg-slate-900/40' }} text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_materno" value="Apellido materno (opcional)" />
                                    <input
                                        id="apellido_materno"
                                        name="apellido_materno"
                                        type="text"
                                        value="{{ old('apellido_materno', $user->apellido_materno) }}"
                                        @if(!$esAdminOCeo) readonly @endif
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 {{ $esAdminOCeo ? 'bg-white/90 dark:bg-slate-900/80' : 'bg-slate-100/80 dark:bg-slate-900/40' }} text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento (opcional)" />
                                    <input
                                        id="fecha_nacimiento"
                                        name="fecha_nacimiento"
                                        type="date"
                                        value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d')) }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/80 text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Foto de perfil
                            </h4>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <div>
                                    @if($user->foto_perfil)
                                        <img
                                            src="{{ asset('storage/' . $user->foto_perfil) }}"
                                            alt="Foto de perfil"
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-slate-200/80 dark:border-slate-700/80 shadow-sm shadow-slate-900/20"
                                        >
                                    @else
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-200/70 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-center text-[0.7rem] text-slate-500 dark:text-slate-400">
                                            Sin foto
                                        </div>
                                    @endif
                                </div>

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
                                        Formatos permitidos: JPG, PNG. Tamano maximo: {{ $esAdminOCeo ? '20 MB' : '2 MB' }}.
                                    </p>

                                    @error('foto_perfil')
                                        <p class="text-[0.7rem] text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Credenciales de acceso
                            </h4>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="email" value="Email *" />
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email', $user->email) }}"
                                        @if(!$esAdminOCeo) readonly @endif
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 {{ $esAdminOCeo ? 'bg-white/90 dark:bg-slate-900/80' : 'bg-slate-100/80 dark:bg-slate-900/40' }} text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                    >
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="password" value="Nueva contrasena (opcional)" />
                                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                                        <p class="mt-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                            Dejalo vacio si no deseas cambiar la contrasena.
                                        </p>
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" value="Confirmar contrasena" />
                                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Rol y estructura
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="role_preview" value="Rol (solo lectura)" />
                                    <input
                                        id="role_preview"
                                        type="text"
                                        value="{{ $user->role?->nombre ?? 'Sin rol' }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                        readonly
                                    >
                                </div>

                                <div>
                                    <x-input-label for="departamento_preview" value="Departamento (solo lectura)" />
                                    <input
                                        id="departamento_preview"
                                        type="text"
                                        value="{{ $user->departamento?->nombre ?? 'Sin departamento' }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                        readonly
                                    >
                                </div>

                                <div>
                                    <x-input-label for="puesto_preview" value="Puesto (solo lectura)" />
                                    <input
                                        id="puesto_preview"
                                        type="text"
                                        value="{{ $user->puesto?->nombre ?? 'Sin puesto' }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                        readonly
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <a href="{{ route('profile.show') }}" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 inline-flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
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
    </x-tb-background>
</x-app-layout>
