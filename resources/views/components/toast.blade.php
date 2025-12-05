@php
    $toast = session('toast'); // ['title' => '...', 'message' => '...']
@endphp

@if ($toast)
<div
    x-data="{
        open: false,
        init() {
            this.$nextTick(() => {
                this.open = true;
                setTimeout(() => this.open = false, 4000);
            });
        }
    }"
    x-show="open"
    x-cloak

    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-3 scale-95"

    class="fixed left-1/2 z-[9999] pointer-events-none transform -translate-x-1/2 translate-x-10"
    style="top: 1.5rem;"
    aria-live="assertive"
>
    <div
        class="pointer-events-auto
               rounded-2xl
               bg-slate-900/95
               border border-[#FF9521]
               shadow-xl shadow-black/50
               px-6 py-2.5
               max-w-lg w-full
               flex items-start gap-4
               backdrop-blur-2xl
               text-[15px]"
    >
        {{-- Barra lateral naranja --}}
        <div class="w-1.5 h-7 rounded-full bg-[#FF9521] mt-0.5"></div>

        {{-- Texto --}}
        <div class="flex-1 space-y-0.5">
            <p class="text-[15px] font-semibold text-slate-50 leading-snug">
                {{ $toast['title'] ?? 'Operación realizada correctamente' }}
            </p>

            @if(!empty($toast['message'] ?? null))
                <p class="text-[13px] text-slate-300 leading-tight">
                    {{ $toast['message'] }}
                </p>
            @endif
        </div>

        {{-- Botón cerrar --}}
        <button
            type="button"
            class="ml-2 text-slate-400 hover:text-slate-100 transition text-[14px]"
            @click="open = false"
        >
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0
                    111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0
                    01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>
@endif
