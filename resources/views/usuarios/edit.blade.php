<x-app-layout>
    <x-tb-background>
        @php
            $canEditDepartment = $canEditDepartment ?? false;
            $departamentos = $departamentos ?? collect();
            $puestoPreviewByDepartamento = $puestoPreviewByDepartamento ?? [];
            $selectedDeptId = (string) old('departamento_id', $user->departamento_id);
            $puestoPreview = $puestoPreviewByDepartamento[$selectedDeptId] ?? ($user->puesto?->nombre ?? 'Sin puesto');
        @endphp

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6">
                <x-topbar
                    title="Editar Usuario"
                    chip="Usuarios · Editar"
                    description="Actualiza la informacion del usuario seleccionado."
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
                                Datos del usuario
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Ajusta solo lo necesario. La contrasena es opcional.
                            </p>
                        </div>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[0.7rem] font-medium bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700/80">
                            ID {{ $user->id }}
                        </span>
                    </div>

                    <form
                        id="edit-user-form"
                        method="POST"
                        action="{{ route('users.update', $user) }}"
                        class="space-y-8"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="confirmar_riesgo_departamento" id="confirmar_riesgo_departamento" value="0">

                        <div class="space-y-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Informacion personal
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="nombre" value="Nombre *" />
                                    <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $user->nombre)" required autofocus />
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="segundo_nombre" value="Segundo nombre (opcional)" />
                                    <x-text-input id="segundo_nombre" class="block mt-1 w-full" type="text" name="segundo_nombre" :value="old('segundo_nombre', $user->segundo_nombre)" />
                                    <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_paterno" value="Apellido paterno *" />
                                    <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $user->apellido_paterno)" required />
                                    <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="apellido_materno" value="Apellido materno (opcional)" />
                                    <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $user->apellido_materno)" />
                                    <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento (opcional)" />
                                    <x-text-input
                                        id="fecha_nacimiento"
                                        class="block mt-1 w-full"
                                        type="date"
                                        name="fecha_nacimiento"
                                        :value="old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d'))"
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
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
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
                                Rol y permisos
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="role_id" value="Asignar rol *" />
                                    <select
                                        id="role_id"
                                        name="role_id"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/80 text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                        required
                                    >
                                        <option value="">-- Seleccionar un rol --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                                </div>

                                <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center">
                                    <p>Solo puedes asignar roles permitidos por tu nivel de acceso.</p>
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
                                    @if($canEditDepartment)
                                        <x-input-label for="departamento_id" value="Departamento *" />
                                        <select
                                            id="departamento_id"
                                            name="departamento_id"
                                            class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-white/90 dark:bg-slate-900/80 text-sm text-slate-800 dark:text-slate-100 shadow-sm shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
                                            required
                                        >
                                            <option value="">-- Seleccionar departamento --</option>
                                            @foreach($departamentos as $dep)
                                                <option value="{{ $dep->id }}" {{ old('departamento_id', $user->departamento_id) == $dep->id ? 'selected' : '' }}>
                                                    {{ $dep->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-2 text-[0.7rem] text-amber-600 dark:text-amber-300">
                                            Cambiar el departamento afecta alcance operativo y asignacion de puesto.
                                        </p>
                                        <x-input-error :messages="$errors->get('departamento_id')" class="mt-2" />
                                    @else
                                        <x-input-label for="departamento_preview" value="Departamento (solo lectura)" />
                                        <input
                                            id="departamento_preview"
                                            type="text"
                                            value="{{ $user->departamento?->nombre ?? 'Sin departamento' }}"
                                            class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                            readonly
                                        >
                                    @endif
                                </div>

                                <div>
                                    <x-input-label for="puesto_preview" value="Puesto (solo lectura)" />
                                    <input
                                        id="puesto_preview"
                                        type="text"
                                        value="{{ $puestoPreview }}"
                                        class="block mt-1 w-full rounded-xl border border-slate-300/80 dark:border-slate-700/80 bg-slate-100/80 dark:bg-slate-900/40 text-sm text-slate-800 dark:text-slate-100"
                                        readonly
                                    >
                                </div>
                            </div>
                        </div>

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

        @if($canEditDepartment)
            <div id="dept-risk-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-lg rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white dark:bg-slate-900 p-6 shadow-2xl">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Riesgo por cambio de departamento</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                        Estás por mover al usuario a otro departamento. Esto puede alterar permisos, asignaciones y métricas históricas.
                        Confirma solo si el cambio es intencional.
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="dept-risk-cancel" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-sm text-slate-700 dark:text-slate-200">
                            Cancelar
                        </button>
                        <button type="button" id="dept-risk-confirm" class="px-4 py-2 rounded-xl bg-amber-600 text-sm font-medium text-white hover:bg-amber-700">
                            Confirmar cambio
                        </button>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('edit-user-form');
                    const dept = document.getElementById('departamento_id');
                    const puesto = document.getElementById('puesto_preview');
                    const confirmInput = document.getElementById('confirmar_riesgo_departamento');
                    const modal = document.getElementById('dept-risk-modal');
                    const btnCancel = document.getElementById('dept-risk-cancel');
                    const btnConfirm = document.getElementById('dept-risk-confirm');
                    const map = @json($puestoPreviewByDepartamento);
                    const initialDept = String(@json((string) $user->departamento_id));

                    if (!form || !dept || !confirmInput || !modal) return;

                    const updatePuesto = () => {
                        const key = String(dept.value || '');
                        puesto.value = map[key] || 'Sin puesto configurado';
                    };

                    dept.addEventListener('change', function () {
                        confirmInput.value = '0';
                        updatePuesto();
                    });
                    updatePuesto();

                    form.addEventListener('submit', function (event) {
                        const currentDept = String(dept.value || '');
                        const changed = currentDept !== initialDept;
                        const confirmed = confirmInput.value === '1';

                        if (changed && !confirmed) {
                            event.preventDefault();
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                        }
                    });

                    btnCancel?.addEventListener('click', function () {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    });

                    btnConfirm?.addEventListener('click', function () {
                        confirmInput.value = '1';
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        form.submit();
                    });
                });
            </script>
        @endif
    </x-tb-background>
</x-app-layout>
