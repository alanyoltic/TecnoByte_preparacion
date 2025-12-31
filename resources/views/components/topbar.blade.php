@props([
    'title' => '',
    'chip' => null,
    'description' => null, // para textos simples (sin @if ni <br>)
])

{{-- HEADER GLASS ESTÁNDAR --}}
<div
    class="relative overflow-hidden mb-6
           rounded-3xl
           bg-white/80 dark:bg-slate-950/70
           border border-slate-200/80 dark:border-white/10
           shadow-lg shadow-slate-900/10 dark:shadow-2xl dark:shadow-slate-950/70
           backdrop-blur-xl dark:backdrop-blur-2xl
           px-6 sm:px-8 lg:px-10 py-4 sm:py-5"
>
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">

        {{-- IZQUIERDA --}}
        <div class="space-y-1.5">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-50 leading-tight">
                    {{ $title }}
                </h2>

                @if(!empty($chip))
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full
                               text-[0.7rem] font-semibold tracking-wide
                               bg-[#FF9521]/10 text-[#FF9521]
                               border border-[#FF9521]/40"
                    >
                        {{ $chip }}
                    </span>
                @endif
            </div>

            {{-- ✅ Descripción: primero slot (para Blade/HTML), si no existe usa prop simple --}}
            @if(isset($desc) && trim((string) $desc) !== '')
                <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    {{ $desc }}
                </div>
            @elseif(!empty($description))
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    {{ $description }}
                </p>
            @endif
        </div>

        {{-- DERECHA --}}
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            {{ $right ?? '' }}
        </div>

    </div>
</div>
