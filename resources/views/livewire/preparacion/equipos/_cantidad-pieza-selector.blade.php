<div class="space-y-1">
    <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">
        Cantidad <span class="text-red-500">*</span>
    </label>
    <div class="flex items-center gap-2 max-w-[160px]">
        <button type="button"
            wire:click="$set('cantidadPieza', {{ max(1, $cantidadPieza - 1) }})"
            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                   bg-slate-200/80 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200
                   hover:bg-amber-100 dark:hover:bg-amber-900/30 text-base font-bold transition-colors">
            −
        </button>
        <input type="number" wire:model.live="cantidadPieza"
            min="1" max="99"
            class="w-full rounded-xl px-3 py-2 text-sm text-center font-bold
                   bg-white/70 dark:bg-slate-900/40
                   border border-slate-300/80 dark:border-slate-700
                   text-slate-900 dark:text-slate-100
                   focus:ring-2 focus:ring-[#FF9521] outline-none">
        <button type="button"
            wire:click="$set('cantidadPieza', {{ min(99, $cantidadPieza + 1) }})"
            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                   bg-slate-200/80 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200
                   hover:bg-amber-100 dark:hover:bg-amber-900/30 text-base font-bold transition-colors">
            +
        </button>
    </div>
</div>
