<x-app-layout>

    
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
                        {{-- Icono usuarios --}}
                        <div
                            class="mt-1 inline-flex items-center justify-center w-9 h-9 rounded-2xl
                                   bg-gradient-to-tr from-indigo-500 via-blue-500 to-sky-400
                                   text-white shadow-[0_0_18px_rgba(59,130,246,0.6)]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                                         a3 3 0 00-.879-2.121M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2
                                         a3 3 0 01.879-2.121M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3
                                         a3 3 0 11-6 0 3 3 0 016 0zM9 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Administrar Usuarios
                            </h2>
                            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Consulta, edita y gestiona los usuarios registrados en el sistema.
                            </p>
                        </div>
                    </div>

                    {{-- Etiqueta de cantidad --}}
                    <span
                        class="hidden sm:inline-flex items-center px-3 py-1 rounded-full
                               text-[0.7rem] font-medium tracking-wide
                               bg-slate-100/80 dark:bg-slate-800/80
                               text-slate-600 dark:text-slate-200
                               border border-slate-200/80 dark:border-slate-700/80"
                    >
                        Total: {{ $usuarios->count() }} usuarios
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
                class=" max-w-7xl mx-auto
                       rounded-3xl
                       bg-white/80 dark:bg-slate-900/80
                       backdrop-blur-2xl
                       border border-white/50 dark:border-slate-800/80
                       shadow-2xl shadow-slate-900/60
                       px-4 sm:px-6 lg:px-8 py-6 sm:py-8
                       space-y-6"
            >

                {{-- Encabezado interno y botón --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-50">
                            Lista de usuarios
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Aquí puedes ver los usuarios activos y su rol actual.
                        </p>
                    </div>

                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium
                               bg-indigo-500/90 hover:bg-indigo-600
                               text-white shadow-md shadow-indigo-500/40
                               transition-all duration-200 hover:-translate-y-0.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo usuario
                    </a>
                </div>

                {{-- Tabla responsiva --}}
                <div class="border border-slate-200/70 dark:border-slate-800/80 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-800/80">
                            <thead class="bg-slate-50/90 dark:bg-slate-900/90">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Nombre
                                    </th>
                                    <th class="px-6 py-3 text-left text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Rol
                                    </th>
                                    <th class="px-6 py-3 text-right text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/80 dark:bg-slate-950/40 divide-y divide-slate-200/60 dark:divide-slate-800/80">
                                @forelse ($usuarios as $usuario)
                                    @php
                                        $roleName = $usuario->role?->nombre ?? 'Sin rol';
                                        $roleSlug = $usuario->role?->slug ?? null;

                                        // Colores de chip según tipo de rol (si tienes slugs así)
                                        if (in_array($roleSlug, ['ceo', 'admin'])) {
                                            $roleClasses = 'bg-rose-500/10 text-rose-400 border border-rose-400/40';
                                        } elseif (in_array($roleSlug, ['supervisor', 'lider'])) {
                                            $roleClasses = 'bg-amber-500/10 text-amber-400 border border-amber-400/40';
                                        } elseif ($roleSlug) {
                                            $roleClasses = 'bg-emerald-500/10 text-emerald-400 border border-emerald-400/40';
                                        } else {
                                            $roleClasses = 'bg-slate-500/10 text-slate-400 border border-slate-400/40';
                                        }
                                    @endphp

                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/60 transition-colors">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-800 dark:text-slate-100">
                                            {{ $usuario->nombre }} {{ $usuario->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                            {{ $usuario->email }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.7rem] font-medium {{ $roleClasses }}">
                                                {{ $roleName }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <a
                                                href="{{ route('users.edit', $usuario) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium
                                                       text-indigo-500 hover:text-indigo-100
                                                       bg-indigo-500/10 hover:bg-indigo-500/90
                                                       border border-indigo-500/40
                                                       transition-all duration-200"
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

</x-app-layout>
