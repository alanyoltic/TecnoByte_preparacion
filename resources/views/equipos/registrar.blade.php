<x-app-layout>
    {{-- HEADER IGUAL QUE EL DASHBOARD PERO PARA REGISTRO --}}
    <x-slot name="header">
        <div class="-mx-4 sm:-mx-6 lg:-mx-8">
            <div
                class="px-4 sm:px-6 lg:px-8 py-3
                       bg-gradient-to-r 
                           from-slate-100/90 via-slate-200/95 to-slate-100/90
                       dark:from-slate-900/95 dark:via-slate-950/95 dark:to-slate-900/95
                       backdrop-blur-xl
                       border-b border-slate-200/70 dark:border-slate-800/80
                       shadow-md shadow-slate-900/40"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Registro de equipos
                            </h2>

                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                       text-[0.65rem] font-medium tracking-wide
                                       bg-indigo-500/10 text-indigo-600
                                       dark:bg-indigo-400/15 dark:text-indigo-200
                                       border border-indigo-500/25"
                            >
                                Taller · Entrada
                            </span>
                        </div>

                        <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Captura de equipos que ingresan a preparación.
                        </p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        {{-- Aquí podrás poner algún botón después si quieres --}}
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- FONDO IGUAL QUE EL DASHBOARD --}}
    <div class="relative py-10 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300
                dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 min-h-screen overflow-hidden">

        <div class="pointer-events-none absolute inset-0 
                    bg-white/10 dark:bg-white/5 
                    backdrop-blur-2xl">
        </div>

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                {{-- AQUÍ VA EL COMPONENTE LIVEWIRE --}}
                <livewire:equipos.registrar-equipo />
            </div>
        </div>
    </div>
</x-app-layout>
