<x-app-layout>


    <x-tb-background>
            {{-- CONTENIDO --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

                {{-- TARJETA HEADER --}}
                            <x-topbar
                    title="Registrar Lote"
                    chip="Lotes · Registrar lotes"
                    description="Crea un nuevo lote y asígnalo a un proveedor para poder registrar sus equipos."
                />

                
                <div class="">

                    {{-- TARJETA FORMULARIO --}}
                    <div
                        class="rounded-3xl
                            bg-white/80 dark:bg-slate-950/70
                            border border-slate-200/80 dark:border-white/10
                            shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-6 sm:px-8 lg:px-10 py-6"
                    >
                        <livewire:lotes.registrar-lote />
                    </div>

                </div>
            </div>
    </x-tb-background>
    
</x-app-layout>
