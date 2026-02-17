<x-app-layout>


<x-tb-background>
        {{-- CONTENIDO: HEADER + TABLA / LISTA INVENTARIO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">

            <x-topbar
                title="Inventario listo"
                chip="Inventario · Equipos registrados"
                description="Consulta y filtra los equipos que ya están capturados en el sistema."
            />


            {{-- CONTENIDO PRINCIPAL: LIVEWIRE INVENTARIO LISTO --}}
            <div class="">
                <livewire:inventario.inventario-listo />
            </div>
        </div>
</x-tb-background>    
</x-app-layout>
