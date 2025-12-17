<!DOCTYPE html>
<html 
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="text-[18px]"
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            // Si el usuario ya tenía activado darkMode, aplica la clase dark INMEDIATAMENTE
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        </script>


        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

<style>
    html, body {
        
        background-color: #020617; /* parecido a slate-950 */
    }

    [x-cloak] { 
        display: none !important; 
    }
</style>

    </head>

<body class="font-sans text-lg antialiased overflow-x-hidden bg-transparent">

        {{-- FONDO GLOBAL FIJO DETRÁS DE TODO (para evitar el bloque negro) --}}
        <div
            class="fixed inset-0 -z-50
                   bg-gradient-to-br
                   from-slate-100 via-slate-200 to-slate-300
                   dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
        </div>

        {{-- CONTENEDOR PRINCIPAL --}}
        <div 
            x-data="{ sidebarOpen: false }" 
            class="relative min-h-screen flex bg-transparent"
        >
            @include('layouts.sidebar')

            <div 
                class="flex-1 flex flex-col transition-all duration-300 ease-in-out"
                :class="{ 'ml-64': sidebarOpen, 'ml-20': !sidebarOpen }"
            >
                {{-- Header del slot, sin max-w ni fondos extra --}}
                @if (isset($header))
                    <header class="w-full">
                        {{ $header }}
                    </header>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        <style>
            :root {
                /* Modo claro */
                --brand-primary: #FF9521;
            }
        </style>

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
       <div
            x-data="{
                items: [],
                limit: 5,
                timeout: 5000,
                tickMs: 60,
                leaveMs: 180,

                titleFor(type){
                    return (
                        type==='success' ? 'Listo' :
                        type==='error'   ? 'Atención' :
                        type==='warning' ? 'Aviso' : 'Info'
                    );
                },

                fire(payload){
                    const type = payload.type ?? 'success';
                    const message = payload.message ?? '';
                    const title = payload.title ?? this.titleFor(type);

                    const id = (crypto?.randomUUID?.() ?? (Date.now() + '-' + Math.random()));

                    const toast = {
                        id, type, title, message,
                        show: false,

                        // progreso
                        duration: this.timeout,
                        remaining: this.timeout,
                        progress: 100,

                        // control pausa / interval
                        paused: false,
                        timer: null,
                        lastTs: null,
                    };

                    this.items.unshift(toast);
                    if (this.items.length > this.limit) this.items = this.items.slice(0, this.limit);

                    this.$nextTick(() => {
                        const t = this.items.find(x => x.id === id);
                        if (!t) return;
                        t.show = true;
                        this.startTimer(t);
                    });
                },

                startTimer(t){
                    if (t.timer) clearInterval(t.timer);
                    t.lastTs = Date.now();

                    t.timer = setInterval(() => {
                        if (t.paused) { t.lastTs = Date.now(); return; }

                        const now = Date.now();
                        const delta = now - (t.lastTs ?? now);
                        t.lastTs = now;

                        t.remaining = Math.max(0, t.remaining - delta);
                        t.progress = Math.round((t.remaining / t.duration) * 100);

                        if (t.remaining <= 0) this.hide(t.id);
                    }, this.tickMs);
                },

                pause(t){
                    t.paused = true;
                },

                resume(t){
                    t.paused = false;
                    t.lastTs = Date.now();
                },

                hide(id){
                    const t = this.items.find(x => x.id === id);
                    if (!t) return;

                    // parar timer
                    if (t.timer) { clearInterval(t.timer); t.timer = null; }

                    t.show = false;

                    setTimeout(() => {
                        this.items = this.items.filter(x => x.id !== id);
                    }, this.leaveMs + 30);
                }
            }"
            x-on:toast.window="fire($event.detail)"
            class="fixed top-5 right-0 pr-2 z-[9999] space-y-3"
            x-cloak
        >
            <template x-for="t in items" :key="t.id">
                <div
                    x-show="t.show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-180"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    class="w-[420px] rounded-2xl border border-white/10
                        bg-slate-950/70 backdrop-blur-xl
                        shadow-2xl shadow-slate-950/60
                        px-6 py-5 flex items-start gap-3 relative overflow-hidden"
                    @mouseenter="pause(t)"
                    @mouseleave="resume(t)"
                >
                    {{-- indicador --}}
                    <div class="mt-1 w-2.5 h-2.5 rounded-full shrink-0"
                        :class="t.type==='success'?'bg-emerald-400':t.type==='error'?'bg-rose-400':t.type==='warning'?'bg-amber-400':'bg-sky-400'"></div>

                    {{-- texto --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-lg font-semibold text-slate-100 leading-tight" x-text="t.title"></p>
                        <p class="text-sm text-slate-300 mt-0.5 leading-snug" x-text="t.message"></p>
                    </div>

                    {{-- cerrar --}}
                    <button type="button"
                            class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10
                                border border-white/10 text-slate-200 flex items-center justify-center shrink-0"
                            @click="hide(t.id)">✕</button>

                    {{-- barra de tiempo --}}
                    <div class="absolute left-0 bottom-0 h-[3px] w-full bg-white/10">
                        <div class="h-full"
                            :class="t.type==='success'?'bg-emerald-400':t.type==='error'?'bg-rose-400':t.type==='warning'?'bg-amber-400':'bg-sky-400'"
                            :style="`width:${t.progress}%; transition: width ${tickMs}ms linear;`">
                        </div>
                    </div>
                </div>
            </template>
        </div>




    </body>
</html>
