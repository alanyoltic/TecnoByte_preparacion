<x-app-layout>
    <x-tb-background>

       

            {{-- CONTENIDO: HEADER + LIVEWIRE --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

                <x-topbar
                    title="Gestión avanzada de inventario"
                    chip="Inventario · Gestión"
                    description="Filtra, revisa y prepara lotes completos para reportes, ajustes masivos y descargas."
                />

                {{-- NOTIFICACIÓN FLOTANTE --}}
                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 4000)"
                        x-show="show"
                        x-transition
                        class="fixed top-4 right-4 z-[9999] max-w-sm
                            rounded-2xl border border-emerald-300/70
                            bg-emerald-50/95 dark:bg-emerald-900/90
                            shadow-lg shadow-emerald-500/30
                            backdrop-blur-md
                            px-4 py-3 flex items-start gap-3"
                    >
                        {{-- Icono --}}
                        <div class="mt-0.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        {{-- Texto --}}
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-100">
                                {{ session('success') }}
                            </p>
                            <p class="text-xs text-emerald-700/90 dark:text-emerald-200/90 mt-0.5">
                                La operación sobre el inventario se completó correctamente.
                            </p>
                        </div>

                        {{-- Cerrar --}}
                        <button
                            type="button"
                            class="ml-1 text-emerald-700/70 hover:text-emerald-900 dark:text-emerald-200/80 dark:hover:text-emerald-50"
                            @click="show = false"
                        >
                            ✕
                        </button>
                    </div>
                @endif

                {{-- CONTENEDOR PRINCIPAL --}}
                <div class="">
                    @livewire('inventario.gestion-inventario')
                </div>

            </div>
        
    </x-tb-background>
</x-app-layout>
