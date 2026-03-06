<x-app-layout>


<x-tb-background>
        {{-- CONTENIDO: HEADER + TABLA / LISTA INVENTARIO --}}
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-10">
            

            <x-topbar
                title="Transferencia de inventario"
                chip="Inventario · Transferencias"
                description="Crea transferencias de inventario entre tus sucursales. Selecciona los productos, cantidades y sucursales de origen y destino para gestionar eficientemente tu stock."

                
            />
        <a href="{{ route('inventario.transferencias') }}"
           class="px-4 py-2 rounded-2xl bg-slate-200/70 dark:bg-slate-800/70
                  text-slate-700 dark:text-slate-200
                  hover:bg-slate-300/70 dark:hover:bg-slate-700/70 transition-all">
            Volver
        </a>

            


            {{-- CONTENIDO PRINCIPAL: LIVEWIRE INVENTARIO LISTO --}}
            <div class="">
                <livewire:inventario.transferencias-crear />
            </div>
        </div>
</x-tb-background>    
</x-app-layout>
