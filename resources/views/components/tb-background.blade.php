@props([
    'poll' => null,
    // si algún día quieres forzar glows desde afuera, todavía puedes:
    'glows' => null,
    // si quieres diferentes glows por vista, puedes cambiarlo: 'tb_glows_' . request()->route()->getName()
    'sessionKey' => 'tb_glows',
])

@php
    // 1) Si te pasan glows, úsalo (override)
    if (is_array($glows)) {
        $g = $glows;
    } else {
        // 2) Si no, intenta leerlos de sesión
        $g = session($sessionKey);

        // 3) Si no existen aún, créalos UNA VEZ y guárdalos
        if (!is_array($g)) {
            $g = [
                'glow1Top'         => rand(-420, -260),
                'glow1Left'        => rand(-320, -120),
                'glow2Bottom'      => rand(-420, -260),
                'glow2Right'       => rand(-320, -120),
                'glow3Bottom'      => rand(-340, -220),
                'glow3LeftPercent' => rand(30, 70),
            ];

            session([$sessionKey => $g]);
        }
    }
@endphp

<div
    class="relative min-h-screen overflow-hidden
           bg-gradient-to-br
           from-slate-100 via-slate-100 to-slate-200
           dark:from-slate-950 dark:via-[#020617] dark:to-slate-950"
    @if($poll) wire:poll.visible.30s="{{ $poll }}" @endif
>
    {{-- Luces TecnoByte (persistentes por sesión) --}}
    <div class="pointer-events-none absolute -inset-2">
        <div
            class="absolute w-[1100px] h-[1100px]
                   bg-[#1E3A8A] rounded-full blur-[240px]
                   opacity-70 md:opacity-90 mix-blend-screen"
            style="top: {{ $g['glow1Top'] }}px; left: {{ $g['glow1Left'] }}px;"
        ></div>

        <div
            class="absolute w-[1000px] h-[1000px]
                   bg-[#0F1A35] rounded-full blur-[240px]
                   opacity-70 md:opacity-95 mix-blend-screen"
            style="bottom: {{ $g['glow2Bottom'] }}px; right: {{ $g['glow2Right'] }}px;"
        ></div>

        <div
            class="absolute w-[850px] h-[850px]
                   bg-[#FF9521]/40 md:bg-[#FF9521]/50
                   rounded-full blur-[260px]
                   opacity-80 md:opacity-90 mix-blend-screen"
            style="bottom: {{ $g['glow3Bottom'] }}px; left: {{ $g['glow3LeftPercent'] }}%;"
        ></div>
    </div>

    <div class="pointer-events-none absolute -inset-2 bg-white/40 dark:bg-slate-950/30 backdrop-blur-2xl transform-gpu will-change-transform"></div>

    <div class="relative z-10 w-full">
        {{ $slot }}
    </div>

</div>
