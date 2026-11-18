<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">
        <x-topbar
            title="Aprobaciones de Dirección"
            chip="CEO > Autorizaciones Financieras"
            description="Órdenes de compra que requieren autorización financiera."
        />

        <div class="overflow-hidden bg-white/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-xl dark:backdrop-blur-2xl shadow-md shadow-slate-900/10 dark:shadow-lg dark:shadow-slate-900/30 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200/80 dark:divide-white/10">
                    <thead class="bg-slate-100/50 dark:bg-slate-900/30">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-left text-slate-500 dark:text-slate-400 uppercase">Folio / Fecha</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-left text-slate-500 dark:text-slate-400 uppercase">Proveedor</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-left text-slate-500 dark:text-slate-400 uppercase">Solicitante</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-right text-slate-500 dark:text-slate-400 uppercase">Total a Pagar</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-center text-slate-500 dark:text-slate-400 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-white/10">
                        @forelse($ordenes as $orden)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-black text-slate-900 dark:text-white">{{ $orden->folio }}</div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($orden->fecha_compra)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ optional($orden->proveedor)->nombre_empresa ?? 'Sin Proveedor' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ optional($orden->registradoPor)->name }}</div>
                                    <div class="mt-1">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $orden->area === 'VENTAS' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                            Área: {{ $orden->area }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-lg font-black text-slate-900 dark:text-white">${{ number_format($orden->total, 2) }}</div>
                                    <div class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 mt-0.5 uppercase tracking-wider">{{ $orden->moneda ?? 'MXN' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('ceo.aprobaciones.revision', $orden->id) }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Revisar Orden
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800/50 mb-4">
                                        <svg class="h-8 w-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <h3 class="text-base font-black text-slate-800 dark:text-white">¡Todo al día!</h3>
                                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">No hay órdenes de compra pendientes de autorización.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tb-background>
