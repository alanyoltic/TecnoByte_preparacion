<x-app-layout>

    
<x-tb-background>

            {{-- CONTENIDO: HEADER + TABLA LIVEWIRE --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

                {{-- HEADER GLASS PARA REGISTRO --}}
                <x-topbar
                    title="Equipos pendientes por piezas"
                    chip="Preparación · Piezas"
                    description="Equipos que no pueden darse por terminados porque están esperando la compra o instalación de una pieza."
                />

                {{-- CONTENIDO PRINCIPAL: LIVEWIRE --}}
                <div class="max-w-7xl mx-auto">
                    <livewire:inventario.pendientes-piezas />
                </div>
            </div>
</x-tb-background>

        
</x-app-layout>
