<x-app-layout>

    
    <x-tb-background>
            {{-- CONTENIDO --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

                {{-- TARJETA HEADER --}}
                            <x-topbar
                    title="Editar lotes"
                    chip="Lotes ·Editar lotes"
                    description="Selecciona un lote para entrar a su edición."
                />

                {{-- CONTENEDOR --}}
                <div class="max-w-7xl mx-auto">
                    <livewire:lotes.lista-lotes />
                </div>

            </div>
    </x-tb-background>

</x-app-layout>
