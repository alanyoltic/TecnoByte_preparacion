<x-app-layout>

   <x-tb-background>
            {{-- CONTENIDO PRINCIPAL --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
                

                    {{-- HEADER GLASS DE LA VISTA --}}
                                        {{-- TARJETA HEADER --}}
                                                <x-topbar
                            title="Administrar Usuarios"
                            chip="Usuarios · Administrar"
                            description="Consulta, edita y gestiona los usuarios registrados en el sistema."
                        />
                <div class="max-w-7xl mx-auto space-y-8">
                    {{-- CARD PRINCIPAL --}}
                    <div
                        class="rounded-3xl
                            bg-white/85 dark:bg-slate-950/75
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            shadow-xl shadow-slate-900/15 dark:shadow-2xl dark:shadow-slate-950/70
                            px-4 sm:px-6 lg:px-8 py-6 sm:py-8
                            space-y-6"
                    >

                        {{-- ENCABEZADO + BOTÓN --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                    Lista de usuarios
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Aquí puedes ver los usuarios activos y su rol actual.
                                </p>
                            </div>

                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                                    bg-blue-600 hover:bg-blue-500
                                    text-white shadow-md shadow-blue-800/30
                                    transition-all duration-200 hover:-translate-y-0.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo usuario
                            </a>
                        </div>

                        {{-- TABLA --}}
                        <div
                            class="rounded-2xl overflow-hidden
                                bg-white/70 dark:bg-slate-950/60
                                border border-slate-200/70 dark:border-slate-800/70
                                shadow-lg dark:shadow-xl dark:shadow-slate-950/60
                                backdrop-blur-xl dark:backdrop-blur-2xl
                                transition-all duration-300"
                        >
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200/60 dark:divide-slate-800/70">
                                    <thead class="bg-slate-100/90 dark:bg-slate-900/70">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-[0.7rem] uppercase tracking-wide font-semibold text-slate-600 dark:text-slate-300">
                                                Nombre
                                            </th>
                                            <th class="px-6 py-3 text-left text-[0.7rem] uppercase tracking-wide font-semibold text-slate-600 dark:text-slate-300">
                                                Email
                                            </th>
                                            <th class="px-6 py-3 text-left text-[0.7rem] uppercase tracking-wide font-semibold text-slate-600 dark:text-slate-300">
                                                Rol
                                            </th>
                                            <th class="px-6 py-3 text-right text-[0.7rem] uppercase tracking-wide font-semibold text-slate-600 dark:text-slate-300">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/70">
                                        @forelse ($usuarios as $usuario)
                                            @php
                                                $roleName = $usuario->role?->nombre ?? 'Sin rol';
                                                $roleSlug = $usuario->role?->slug ?? null;

                                                if (in_array($roleSlug, ['ceo','admin'])) {
                                                    $chip = 'bg-rose-500/10 text-rose-300 border border-rose-400/40';
                                                } elseif (in_array($roleSlug, ['supervisor','lider'])) {
                                                    $chip = 'bg-amber-500/10 text-amber-300 border border-amber-400/40';
                                                } elseif ($roleSlug) {
                                                    $chip = 'bg-emerald-500/10 text-emerald-300 border border-emerald-400/40';
                                                } else {
                                                    $chip = 'bg-slate-500/10 text-slate-300 border border-slate-400/40';
                                                }
                                            @endphp

                                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/60 transition-colors">
                                                <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-50">
                                                    {{ $usuario->nombre }} {{ $usuario->apellido_paterno }}
                                                </td>

                                                <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">
                                                    {{ $usuario->email }}
                                                </td>

                                                <td class="px-6 py-3">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium {{ $chip }}">
                                                        {{ $roleName }}
                                                    </span>
                                                </td>

                                                <td class="px-6 py-3 text-right">
                                                    <a
                                                        href="{{ route('users.edit', $usuario) }}"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                                            text-blue-400 bg-blue-500/10 border border-blue-400/40
                                                            hover:bg-blue-500/80 hover:text-white transition-all duration-200"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11
                                                                    a2 2 0 002 2h11a2 2 0 002-2v-5
                                                                    m-1.414-9.414a2 2 0 112.828 2.828L12 18l-4 1 1-4 9.586-9.586z" />
                                                        </svg>
                                                        Editar
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                                    No hay usuarios registrados por el momento.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

    </x-tb-background>

</x-app-layout>
