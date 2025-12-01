<x-app-layout>
    @php
        // Aseguramos que siempre haya un $user (por si no lo pasan desde el controlador)
        $user = $user ?? Auth::user();
    @endphp

    {{-- FONDO ESTILO DASHBOARD / LOGIN --}}
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-200 to-slate-300
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >
        {{-- LUCES GLOW --}}
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

        {{-- CAPA GLASS GENERAL --}}
        <div class="absolute inset-0 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl"></div>

        {{-- CONTENIDO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            <div class="space-y-6 max-w-6xl mx-auto">

                {{-- HEADER (TÍTULO DE LA PÁGINA) --}}
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
                        <div>
                            <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Mi perfil
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                Aquí puedes consultar tus datos personales registrados en el sistema.
                            </p>
                        </div>

                        <span
                            class="hidden sm:inline-flex items-center px-3 py-1 rounded-full
                                   text-[0.7rem] font-medium tracking-wide
                                   bg-slate-100/80 dark:bg-slate-800/80
                                   text-slate-600 dark:text-slate-200
                                   border border-slate-200/80 dark:border-slate-700/80"
                        >
                            Usuario: {{ $user->email }}
                        </span>
                    </div>
                </div>

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
                                   bg-slate-900/90 dark:bg-slate-950/95
                                   border border-slate-800/80
                                   py-8 px-4 space-y-4"
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
                                <p class="text-xs font-medium tracking-[0.18em] uppercase text-slate-400">
                                    Nombre(s) y apellidos
                                </p>
                                <p class="text-sm sm:text-base font-semibold text-slate-50">
                                    {{ trim($user->nombre.' '.$user->segundo_nombre.' '.$user->apellido_paterno.' '.$user->apellido_materno) ?: 'Sin nombre' }}
                                </p>
                            </div>
                        </div>

                        {{-- COLUMNA DERECHA: DATOS + BOTÓN EDITAR --}}
                        <div
                            class="relative rounded-3xl
                                   bg-slate-950/85
                                   border border-slate-800/80
                                   px-5 sm:px-7 py-6 sm:py-7
                                   flex flex-col justify-between"
                        >
                            {{-- Botón EDITAR arriba a la derecha --}}
                            <div class="flex justify-end mb-4">
                                <a href="{{ route('profile.edit') }}"
                                   class="text-[0.70rem] sm:text-xs font-semibold tracking-[0.16em]
                                          uppercase
                                          px-3 py-1.5 rounded-full
                                          bg-slate-900/80 hover:bg-slate-800/90
                                          text-slate-200 hover:text-white
                                          border border-slate-600/80
                                          shadow-sm shadow-slate-900/60
                                          transition-colors duration-150">
                                    Editar
                                </a>
                            </div>

                            <div class="space-y-4 sm:space-y-5">
                                {{-- FILA: Cargo / Puesto --}}
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)]"></span>
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-wide text-slate-500">Cargo / Puesto</span>
                                        <span class="text-sm sm:text-base font-medium text-slate-50">
                                            {{ $user->role->nombre ?? 'Sin asignar' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- FILA: Fecha de nacimiento --}}
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)]"></span>
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-wide text-slate-500">Fecha de nacimiento</span>
                                        <span class="text-sm sm:text-base font-medium text-slate-50">
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
                                        <span class="text-sm sm:text-base font-medium text-slate-50 break-all">
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
    </div>
</x-app-layout>
