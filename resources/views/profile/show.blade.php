<x-app-layout>
    @php
        // Aseguramos que siempre haya un $user (por si no lo pasan desde el controlador)
        $user = $user ?? Auth::user();
    @endphp

    <x-tb-background>

        {{-- CONTENIDO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
           

                                    {{-- TARJETA HEADER --}}
                                            <x-topbar
                                                title="Mi perfil"
                                                chip="Perfil · Ver"
                                                description="Aquí puedes consultar tus datos personales registrados en el sistema."
                                            />
                 <div class="space-y-6 max-w-6xl mx-auto">

                {{-- TARJETA PRINCIPAL PERFIL (DISEÑO DEL MOCKUP) --}}
                <div
                    class="rounded-3xl
                           bg-white/85 dark:bg-slate-950/80
                           border border-slate-200/80 dark:border-white/10
                           backdrop-blur-xl dark:backdrop-blur-2xl
                           shadow-xl shadow-slate-900/15
                           dark:shadow-2xl dark:shadow-slate-950/70
                           px-5 sm:px-8 py-6 sm:py-8"
                >
                    <div class="grid grid-cols-1 md:grid-cols-[260px_minmax(0,1fr)] gap-6 md:gap-8 items-stretch">

                        {{-- COLUMNA IZQUIERDA: AVATAR + NOMBRE --}}
                            <div
                                class="flex flex-col items-center justify-center
                                    rounded-3xl
                                    bg-white/70 dark:bg-slate-950/95
                                    border border-slate-200/80 dark:border-slate-800/80
                                    py-8 px-4 space-y-4
                                    backdrop-blur-xl"
                            >

                            {{-- Avatar / Foto --}}
                            <div
                                class="w-28 h-28 sm:w-32 sm:h-32 rounded-full
                                       flex items-center justify-center
                                       bg-gradient-to-br from-indigo-500 via-sky-500 to-purple-500
                                       shadow-[0_0_35px_rgba(59,130,246,0.9)]
                                       overflow-hidden"
                            >
                                @if(!empty($user->foto_perfil))
                                    <img
                                        src="{{ asset('storage/'.$user->foto_perfil) }}"
                                        alt="Foto de perfil"
                                        class="w-full h-full object-cover"
                                    />
                                @else
                                    {{-- Placeholder tipo ícono --}}
                                    <svg class="w-16 h-16 text-violet-100/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5.121 17.804A9 9 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Nombre completo --}}
                            <div class="text-center space-y-1">
                                <p class="text-xs font-medium tracking-[0.18em] uppercase text-slate-600 dark:text-slate-400">
                                    Nombre(s) y apellidos
                                </p>
                                <p class="text-sm sm:text-base font-semibold text-slate-900 dark:text-slate-50">
                                    {{ trim($user->nombre.' '.$user->segundo_nombre.' '.$user->apellido_paterno.' '.$user->apellido_materno) ?: 'Sin nombre' }}
                                </p>
                            </div>
                        </div>

                        {{-- COLUMNA DERECHA: DATOS + BOTÓN EDITAR --}}
                            <div
                                class="relative rounded-3xl
                                    bg-white/70 dark:bg-slate-950/85
                                    border border-slate-200/80 dark:border-slate-800/80
                                    px-5 sm:px-7 py-6 sm:py-7
                                    flex flex-col justify-between
                                    backdrop-blur-xl"
                            >

                            {{-- Botón EDITAR arriba a la derecha --}}
                            <div class="flex justify-end mb-4">
                                <a href="{{ route('profile.edit') }}"
                                class="text-[0.70rem] sm:text-xs font-semibold tracking-[0.16em] uppercase
                                        px-3 py-1.5 rounded-full
                                        bg-slate-100/80 hover:bg-slate-200/80
                                        dark:bg-slate-900/80 dark:hover:bg-slate-800/90
                                        text-slate-700 hover:text-slate-900
                                        dark:text-slate-200 dark:hover:text-white
                                        border border-slate-200/80 dark:border-slate-600/80
                                        shadow-sm shadow-slate-900/10 dark:shadow-slate-900/60
                                        transition-colors duration-150">
                                    Editar
                                </a>

                            </div>

                            <div class="space-y-4 sm:space-y-5">
                                {{-- FILA: Cargo / Puesto --}}
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)]"></span>
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Cargo / Puesto</span>
                                        <span class="text-sm sm:text-base font-medium text-slate-900 dark:text-slate-50">
                                            {{ $user->role->nombre ?? 'Sin asignar' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- FILA: Fecha de nacimiento --}}
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)]"></span>
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-wide text-slate-500">Fecha de nacimiento</span>
                                        <span class="text-sm sm:text-base font-medium text-slate-900 dark:text-slate-50">
                                            {{-- Ajusta el campo según tu tabla (ej: fecha_nacimiento) --}}
                                            {{ $user->fecha_nacimiento
                                                ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y')
                                                : 'Sin registrar' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- FILA: Correo --}}
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)]"></span>
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-wide text-slate-500">Correo</span>
                                        <span class="text-sm sm:text-base font-medium text-slate-900 dark:text-slate-50 break-all">
                                            {{ $user->email }}
                                        </span>
                                    </div>
                                </div>



                            {{-- Logo TecnoByte alineado abajo a la derecha (opcional) --}}
                            <div class="mt-6 flex justify-end">
                                <img
                                    src="{{ asset('images/logo-tecnobyte.png') }}"
                                    alt="TecnoByte"
                                    class="h-6 opacity-80"
                                >
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </x-tb-background>
</x-app-layout>
