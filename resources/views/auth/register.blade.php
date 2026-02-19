<x-app-layout>
    <x-tb-background>
        @php
            $isGlobalCreator = $isGlobalCreator ?? false;
            $fixedDepartamento = $fixedDepartamento ?? null;
            $departamentos = $departamentos ?? collect();
            $puestoPreviewByDepartamento = $puestoPreviewByDepartamento ?? [];

            $selectedDeptId = (string) old('departamento_id', $fixedDepartamento->id ?? '');
            $puestoPreview = $puestoPreviewByDepartamento[$selectedDeptId] ?? ($initialPuestoLabel ?? 'Sin puesto configurado');
        @endphp

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6">
                <x-topbar
                    title="Registrar Nuevo Usuario"
                    chip="Usuarios · Registrar"
                    description="Completa la informacion para agregar un nuevo colaborador al sistema."
                />

                <div
                    class="rounded-3xl
                           bg-white/85 dark:bg-slate-950/75
                           border border-slate-200/80 dark:border-white/10
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           shadow-xl shadow-slate-900/15 dark:shadow-2xl dark:shadow-slate-950/70
                           px-5 sm:px-8 py-6 sm:py-8
                           space-y-6"
                >
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

                    <form method="POST" action="{{ route('register') }}" class="space-y-8" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Informacion personal
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="nombre" value="Nombre *" />
                                    <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="segundo_nombre" value="Segundo nombre (opcional)" />
                                    <x-text-input id="segundo_nombre" class="block mt-1 w-full" type="text" name="segundo_nombre" :value="old('segundo_nombre')" />
                                    <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_paterno" value="Apellido paterno *" />
                                    <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                                    <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_materno" value="Apellido materno (opcional)" />
                                    <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" />
                                    <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento (opcional)" />
                                    <x-text-input
                                        id="fecha_nacimiento"
                                        class="block mt-1 w-full"
                                        type="date"
                                        name="fecha_nacimiento"
                                        :value="old('fecha_nacimiento')"
                                    />
                                    <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Foto de perfil
                            </h4>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <div
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl
                                        bg-slate-200/70 dark:bg-slate-800/70
                                        border border-slate-200/80 dark:border-slate-700/80
                                        flex items-center justify-center
                                        text-[0.7rem] text-slate-500 dark:text-slate-400"
                                >
                                    Sin foto
                                </div>

                                <div class="flex-1 space-y-1">
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-200">
                                        Subir foto de perfil (opcional)
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
                                        Formatos permitidos: JPG, PNG. Tamano maximo: 20 MB.
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
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="password" value="Contrasena *" />
                                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" value="Confirmar contrasena *" />
                                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Rol y permisos
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="role_id" value="Asignar rol *" />
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
                                        Segun tu nivel, solo puedes asignar tu mismo rol o uno menor.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/70 dark:border-slate-800/70"></div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Departamento y puesto
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="departamento_id" value="Departamento *" />

                                    @if($isGlobalCreator)
                                        <select
                                            id="departamento_id"
                                            name="departamento_id"
                                            class="block mt-1 w-full rounded-xl
                                                   border border-slate-300/80 dark:border-slate-700/80
                                                   bg-white/90 dark:bg-slate-900/80
                                                   text-sm text-slate-800 dark:text-slate-100
                                                   shadow-sm shadow-slate-900/10
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                            required
                                        >
                                            <option value="">-- Seleccionar departamento --</option>
                                            @foreach($departamentos as $dep)
                                                <option value="{{ $dep->id }}" {{ old('departamento_id') == $dep->id ? 'selected' : '' }}>
                                                    {{ $dep->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            type="text"
                                            value="{{ $fixedDepartamento?->nombre ?? 'Sin departamento' }}"
                                            class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                            readonly
                                        >
                                        <input type="hidden" id="departamento_id" name="departamento_id" value="{{ $fixedDepartamento?->id }}">
                                    @endif

                                    <x-input-error :messages="$errors->get('departamento_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="puesto_preview" value="Puesto asignado" />
                                    <input
                                        id="puesto_preview"
                                        type="text"
                                        value="{{ $puestoPreview }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                        readonly
                                    >
                                    <p class="mt-2 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        El puesto se asigna automaticamente segun departamento y no es editable.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <a
                                href="{{ route('users.index') }}"
                                class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-300 inline-flex items-center gap-1 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Volver al listado
                            </a>

                            <x-primary-button class="ms-4">
                                Crear usuario
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($isGlobalCreator)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const depto = document.getElementById('departamento_id');
                    const puesto = document.getElementById('puesto_preview');
                    const map = @json($puestoPreviewByDepartamento);

                    if (!depto || !puesto) return;

                    const updatePuesto = () => {
                        const key = String(depto.value || '');
                        puesto.value = map[key] || 'Sin puesto configurado';
                    };

                    depto.addEventListener('change', updatePuesto);
                    updatePuesto();
                });
            </script>
        @endif
    </x-tb-background>
</x-app-layout>
