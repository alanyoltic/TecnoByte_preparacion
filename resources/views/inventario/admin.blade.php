<x-app-layout>
    <div
        class="relative min-h-screen overflow-hidden
               bg-gradient-to-br
               from-slate-100 via-slate-200 to-slate-300
               dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            {{-- Aquí podrías reutilizar las luces glow que usas en otras vistas --}}
        </div>

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-50 mb-4">
                Panel de inventario
            </h1>

            {{-- Componente Livewire --}}
            <div
                class="rounded-2xl
                       bg-white/80 dark:bg-slate-950/80
                       border border-slate-200/80 dark:border-white/10
                       backdrop-blur-xl dark:backdrop-blur-2xl
                       shadow-md shadow-slate-900/10
                       dark:shadow-lg dark:shadow-slate-900/30
                       p-4 sm:p-6"
            >
                @livewire('inventario.gestion-equipos')
                {{-- o: <livewire:inventario.gestion-equipos /> --}}
            </div>
        </div>
    </div>
</x-app-layout>
