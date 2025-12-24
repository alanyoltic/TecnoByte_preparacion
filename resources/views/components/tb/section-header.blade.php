@props([
    'text' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => "flex items-center gap-2 $class"]) }}>
    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
        {{ $text ?? $slot }}
    </span>

    <div class="h-px flex-1 bg-gradient-to-r from-slate-300/70 dark:from-slate-700/70 to-transparent"></div>
</div>
