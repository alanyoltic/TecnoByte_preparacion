<x-app-layout>

<x-tb-background>


            {{-- CONTENIDO: HEADER + FORMULARIO (LIVEWIRE) --}}
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

                {{-- HEADER GLASS PARA REGISTRO --}}
                <x-topbar
                    title="Registro de equipos"
                    chip="Preparación · Entrada"
                    description="Captura de equipos que ingresan a preparación."
                />


                {{-- CONTENEDOR PRINCIPAL DEL FORMULARIO --}}
                <div class="">
                    @livewire('inventario.resumen-inventario')

                </div>
            </div>
    
</x-tb-background>

</x-app-layout>
