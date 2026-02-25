<div class="space-y-6 relative">


    <div class="space-y-6 relative">

   
            <x-toast />
            






            {{-- FILA SUPERIOR: RESUMEN + BUSCADOR --}}
            <div class="flex flex-col lg:flex-row gap-6">

                {{-- TARJETAS RESUMEN — ESTILO GLOW REAL --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">

                    {{-- TOTAL EQUIPOS — Glow azul --}}
                    <div
                        class="rounded-2xl
                            bg-white/80 dark:bg-slate-950/60
                            border border-slate-200/80 dark:border-white/10
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-slate-900/10
                            dark:shadow-lg dark:shadow-slate-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-sky-500/20
                            dark:hover:shadow-2xl dark:hover:shadow-sky-500/25
                            hover:border-sky-400/70 dark:hover:border-sky-300/50"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide">
                            Total equipos
                        </p>
                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-50">
                            {{ $stats['total'] ?? 0 }}
                        </p>
                    </div>

                    {{-- EN REVISIÓN — Glow amarillo --}}
                    <div
                        class="rounded-2xl
                            bg-amber-50/90 dark:bg-amber-950/40
                            border border-amber-200/80 dark:border-amber-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-amber-900/10
                            dark:shadow-lg dark:shadow-amber-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-amber-500/40
                            dark:hover:shadow-2xl dark:hover:shadow-amber-400/50
                            hover:border-amber-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-amber-700 dark:text-amber-200 uppercase tracking-wide">
                            En revisión
                        </p>
                        <p class="mt-2 text-2xl font-bold text-amber-800 dark:text-amber-100">
                            {{ $stats['en_revision'] ?? 0 }}
                        </p>
                    </div>

                    {{-- APROBADOS — Glow verde --}}
                    <div
                        class="rounded-2xl
                            bg-emerald-50/90 dark:bg-emerald-950/40
                            border border-emerald-200/80 dark:border-emerald-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-emerald-900/10
                            dark:shadow-lg dark:shadow-emerald-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-emerald-500/40
                            dark:hover:shadow-2xl dark:hover:shadow-emerald-400/50
                            hover:border-emerald-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-emerald-700 dark:text-emerald-200 uppercase tracking-wide">
                            Aprobados
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-800 dark:text-emerald-100">
                            {{ $stats['aprobados'] ?? 0 }}
                        </p>
                    </div>

                    {{-- FINALIZADOS — Glow morado --}}
                    <div
                        class="rounded-2xl
                            bg-indigo-50/90 dark:bg-indigo-950/40
                            border border-indigo-200/80 dark:border-indigo-500/70
                            backdrop-blur-xl dark:backdrop-blur-2xl
                            px-4 py-3
                            shadow-md shadow-indigo-900/10
                            dark:shadow-lg dark:shadow-indigo-900/30
                            transition-all duration-300
                            hover:-translate-y-1
                            hover:shadow-lg hover:shadow-indigo-500/40
                            dark:hover:shadow-2xl dark:hover:shadow-indigo-400/50
                            hover:border-indigo-400/70"
                    >
                        <p class="text-xs sm:text-sm font-semibold text-indigo-700 dark:text-indigo-200 uppercase tracking-wide">
                            Finalizados
                        </p>
                        <p class="mt-2 text-2xl font-bold text-indigo-800 dark:text-indigo-100">
                            {{ $stats['finalizados'] ?? 0 }}
                        </p>
                    </div>

                </div>

                {{-- Buscador --}}
                <div class="w-full lg:w-80">
                    <label class="block text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Búsqueda rápida
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-lg">
                            🔍
                        </span>

                        <input
                            type="text"
                            wire:model.live.debounce.500ms="search"
                            placeholder="Serie, marca, modelo, tipo..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm sm:text-base rounded-2xl
                                bg-white/80 dark:bg-slate-900/60
                                border border-white/60 dark:border-slate-700/70
                                text-slate-900 dark:text-slate-100
                                placeholder:text-slate-400 dark:placeholder:text-slate-500
                                shadow-md shadow-slate-900/10 dark:shadow-xl dark:shadow-slate-950/60
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70 focus:border-blue-500/70
                                backdrop-blur-xl"
                        >
                    </div>
                </div>
            </div>

            {{-- FILTROS --}}
            <div
                class="rounded-2xl
                    bg-white/80 dark:bg-slate-950/70
                    border border-slate-200/80 dark:border-white/10
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30
                    transition-all duration-300
                    hover:-translate-y-1
                    hover:shadow-lg hover:shadow-indigo-500/20
                    dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                    hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50"
            >
                <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Filtros
                    </h3>
                    <p class="hidden sm:block text-sm sm:text-base text-slate-600 dark:text-slate-300">
                        Mostrando
                        <span class="font-bold text-slate-900 dark:text-slate-50">{{ $transferencias->total() }}</span>
                        registro(s)
                        @if($search)
                            para “<span class="font-semibold">{{ $search }}</span>”
                        @endif
                    </p>
                    
                </div>

                

                {{-- Campos de filtros --}}
                <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

                    {{-- Estatus --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                            Estatus general
                        </label>
                        <select
                            wire:model.live="filtroEstado"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                border border-white/60 dark:border-slate-600/70
                                text-sm sm:text-base text-slate-900 dark:text-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                        >
                            <option value="todos">Todos</option>
                            <option value="En Revisión">En Revisión</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Pendiente Pieza">Pendiente Pieza</option>
                            <option value="Pendiente Garantía">Pendiente Garantía</option>
                            <option value="Pendiente Deshueso">Pendiente Deshueso</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    @if(auth()->check() && auth()->user()->tienePermiso('transferencias.crear'))
    <a href="{{ route('inventario.transferencias.crear') }}"
       class="px-4 py-2 rounded-xl bg-indigo-600 text-white">
        Nueva transferencia
    </a>
@endif

                    {{-- Lote --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">
                            Lote
                        </label>
                        <select
                            wire:model.live="filtroLote"
                            class="w-full rounded-2xl bg-white/90 dark:bg-slate-900/70
                                border border-white/60 dark:border-slate-600/70
                                text-sm sm:text-base text-slate-900 dark:text-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                        >

                        </select>
                    </div>



                    </div>


                </div>
            </div>

            {{-- TABLA DE EQUIPOS --}}
            <div
                class="rounded-2xl
                    bg-white/80 dark:bg-slate-950/80
                    border border-slate-200/80 dark:border-white/10
                    backdrop-blur-xl dark:backdrop-blur-2xl
                    shadow-md shadow-slate-900/10
                    dark:shadow-lg dark:shadow-slate-900/30
                    overflow-hidden
                    transition-all duration-300
                    hover:-translate-y-1
                    hover:shadow-lg hover:shadow-indigo-500/20
                    dark:hover:shadow-2xl dark:hover:shadow-indigo-500/25
                    hover:border-[#3B82F6]/70 dark:hover:border-indigo-400/50"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm sm:text-base text-left">

            <thead class="bg-slate-100 dark:bg-slate-950/90">
                <tr>
                    <th class="px-4 py-3">ID Operación</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Origen</th>
                    <th class="px-4 py-3">Destino</th>
                    <th class="px-4 py-3 text-center">Total Productos</th>
                    <th class="px-4 py-3 text-center">Unidades</th>
                    <th class="px-4 py-3 text-center">Estatus</th>
                    
                </tr>
            </thead>
                        

        <tbody>
        @forelse ($transferencias as $mov)

        <tr class="border-b border-slate-200 dark:border-slate-800/80
                hover:bg-white/60 dark:hover:bg-slate-800/60 transition">

            {{-- ID OPERACIÓN --}}
            <td class="px-4 py-3 font-mono font-semibold text-slate-900 dark:text-slate-50">
                #{{ $mov->id }}
            </td>

                        {{-- FECHA --}}
            <td class="px-4 py-3 whitespace-nowrap text-sm">
                {{ $mov->created_at->format('d/m/Y H:i') }}
            </td>

            {{-- ORIGEN --}}
            <td class="px-4 py-3">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                            bg-orange-100 text-orange-800 border border-orange-300">
                    {{ $mov->desde->nombre ?? '—' }}
                </span>
            </td>

            {{-- DESTINO --}}
            <td class="px-4 py-3">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                            bg-blue-100 text-blue-800 border border-blue-300">
                    {{ $mov->hacia->nombre ?? '—' }}
                </span>
            </td>

            {{-- TOTAL PRODUCTOS --}}
            <td class="px-4 py-3 text-center font-semibold">
                1
            </td>

            {{-- UNIDADES --}}
            <td class="px-4 py-3 text-center font-semibold">
                1
            </td>

            {{-- ESTATUS --}}
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                            bg-emerald-100 text-emerald-800 border border-emerald-300">
                    COMPLETADO
                </span>
            </td>



        </tr>

        @empty
        <tr>
            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                No hay transferencias registradas.
            </td>
        </tr>
        @endforelse
        </tbody>

        

                    </table>
                </div>

                {{-- Paginación --}}
                <div class="border-t border-slate-200 dark:border-slate-800/80 px-4 py-3 bg-white/80 dark:bg-slate-950/40">
                    {{ $transferencias->links() }}
                </div>

            </div>



        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

            /* Quitar CUALQUIER borde/sombra del área de impresión */
            #area-impresion-final,
            #area-impresion-final * {
                border: none !important;
                box-shadow: none !important;
                outline: none !important;
            }

            @media print {
                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                }

                /* Solo se muestra el contenedor de impresión */
                body > *:not(#area-impresion-final) {
                    display: none !important;
                }

                /* Página exactamente tamaño etiqueta */
                @page {
                    size: 74mm 50mm;
                    margin: 0;
                }

                /* Contenedor de impresión: pantalla completa, fondo blanco, SIN marco */
                #area-impresion-final {
                    position: fixed !important;
                    inset: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }

                /* Forzar colores (azul/naranja del diseño) */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>







        <style>
        @media print {
        @page { margin: 0; }

        #area-impresion-final {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .titulo-equipo {
            white-space: nowrap !important;
            display: block !important;
            text-align: center !important;
            line-height: 1 !important;
            margin: 0 !important;
            padding: 0 !important;
            letter-spacing: 0.06em; /* mejora legibilidad */
        }
        }
        </style>

        <script>
        /**
        * Convierte el ID a un código óptimo para barcode.
        * - IDs pequeños: numérico
        * - IDs grandes: Base36 (más corto)
        */
        function encodeIdForBarcode(id) {
        const num = Number(id);
        if (!Number.isInteger(num) || num <= 0) return String(id);
        if (num < 100000) return String(num);
        return num.toString(36).toUpperCase();
        }

        /**
        * Ajusta el título para que quepa en una sola línea
        */
        function ajustarTituloParaEtiqueta(tituloEl) {
        if (!tituloEl) return;

        const textoOriginal = (tituloEl.textContent || '').toString().trim();
        const contenedor = tituloEl.parentElement;
        if (!contenedor) return;

        let fontSize = 11;      // pt
        const minFontSize = 8;  // pt

        tituloEl.style.whiteSpace = 'nowrap';
        tituloEl.style.display = 'block';
        tituloEl.style.textAlign = 'center';
        tituloEl.style.lineHeight = '1';
        tituloEl.style.margin = '0';
        tituloEl.style.padding = '0';
        tituloEl.style.fontSize = fontSize + 'pt';
        tituloEl.textContent = textoOriginal;

        void contenedor.offsetWidth;

        const maxWidth = contenedor.clientWidth;

        while (tituloEl.scrollWidth > maxWidth && fontSize > minFontSize) {
            fontSize -= 0.3;
            tituloEl.style.fontSize = fontSize + 'pt';
        }

        if (tituloEl.scrollWidth > maxWidth) {
            let texto = textoOriginal;
            while (texto.length > 4 && tituloEl.scrollWidth > maxWidth) {
            texto = texto.slice(0, -1);
            tituloEl.textContent = texto + '...';
            }
        }
        }

        /**
        * Ajusta el texto del barcode (la serie visible) para que NO sea más ancho que el barcode.
        * Se ejecuta DESPUÉS de JsBarcode.
        */
        function ajustarTextoBarcodeAlAncho(svg, minFontSize = 7, step = 0.5) {
            if (!svg) return;

            // ✅ Evitar que el SVG recorte el texto
            svg.style.overflow = 'visible';
            svg.setAttribute('overflow', 'visible');
            svg.style.display = 'block';
            svg.style.margin = '0 auto';

            const textEl = svg.querySelector('text');
            if (!textEl) return;

            // Forzar layout
            void svg.getBoundingClientRect();

            // Tomamos bbox del SVG (barcode completo)
            let bbox;
            try {
                bbox = svg.getBBox();
            } catch (e) {
                // si fallara getBBox (raro), salimos sin romper
                return;
            }

            const barcodeCenterX = bbox.x + (bbox.width / 2);

            // ✅ Centrar el texto respecto al barcode
            textEl.setAttribute('text-anchor', 'middle');
            textEl.setAttribute('x', String(barcodeCenterX));

            // Tomar font-size actual del <text> generado por JsBarcode
            let currentSize = parseFloat(textEl.getAttribute('font-size')) || 9.5;
            

            // Reducir hasta que el texto quepa dentro del ancho del barcode
            while (currentSize > minFontSize) {
                textEl.setAttribute('font-size', String(currentSize));

                let textWidth = 0;
                try {
                    textWidth = textEl.getBBox().width;
                } catch (e) {
                    break;
                }

                if (textWidth <= bbox.width) break;
                currentSize -= step;
            }
        }


        /**
        * Auto-ajuste de líneas de especificaciones
        */
        function autoResizeSpecLines(container) {
        if (!container) return;

        const lines = container.querySelectorAll('.spec-line');
        lines.forEach(line => {
            let size = 9;
            line.style.fontSize = size + "pt";

            while (line.scrollWidth > container.clientWidth && size > 7) {
            size -= 0.3;
            line.style.fontSize = size + "pt";
            }
        });
        }

        /**
        * FUNCIÓN PRINCIPAL DE IMPRESIÓN
        */
        function imprimirEtiquetaFinal(id) {
        const fuente = document.getElementById('etiqueta-source-' + id);
        if (!fuente) {
            alert('Error: No se encuentra la etiqueta');
            return;
        }

        let area = document.getElementById('area-impresion-final');
        if (!area) {
            area = document.createElement('div');
            area.id = 'area-impresion-final';
            document.body.appendChild(area);
        }

        area.innerHTML = '';
        area.style.position = 'fixed';
        area.style.inset = '0';
        area.style.margin = '0';
        area.style.padding = '0';
        area.style.background = 'white';
        area.style.display = 'flex';
        area.style.alignItems = 'center';
        area.style.justifyContent = 'center';
        area.style.zIndex = '9999';

        const etiqueta = fuente.firstElementChild.cloneNode(true);
        area.appendChild(etiqueta);

        // Título
        const tituloEl = area.querySelector('.titulo-equipo');
        ajustarTituloParaEtiqueta(tituloEl);

        // Specs
        const specBlock = area.querySelector('.spec-block');
        if (specBlock) {
            autoResizeSpecLines(specBlock);
        }

        // Barcode
        const svg = area.querySelector('.barcode-target');
        if (svg) {
            try {
            // Lo que se ESCANEA (compacto)
            const codigoScan = encodeIdForBarcode(id);

            // Lo que se VE debajo (humano)
            const serieHumana = svg.dataset.serie || '';

            JsBarcode(svg, codigoScan, {
                format: "CODE128",
                width: 0.8,
                height: 13,

                displayValue: true,
                text: '*' + serieHumana + '*',  // 👈 visible (serie)
                fontSize: 11,
                fontOptions: "bold",
                textAlign: "center",
                textMargin: 2,
                margin: 6
            });

            // 🔥 Ajuste dinámico del texto visible al ancho del barcode
            ajustarTextoBarcodeAlAncho(svg, 7, 0.5);

            } catch (e) {
            console.error(e);
            }
        }

        window.onafterprint = function () {
            area.style.display = 'none';
            area.innerHTML = '';
            window.onafterprint = null;
        };

        window.print();
        }
        </script>








    </div>
</div>




