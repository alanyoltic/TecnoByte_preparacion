<x-app-layout>
<x-tb-background>
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12 space-y-6">

        {{-- TOPBAR --}}
        <x-topbar
            title="Recursos Humanos"
            chip="Módulo en Planificación"
            description="Administración de talento humano, expedientes, turnos y control de asistencia."
        />

        {{-- TARJETA GLASS INFORMATIVA --}}
        <div class="max-w-2xl mx-auto mt-12 p-8 rounded-3xl bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/10 backdrop-blur-2xl shadow-2xl text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-[#FF9521]/15 border border-[#FF9521]/30 flex items-center justify-center text-[#FF9521] text-3xl shadow-lg shadow-orange-500/20">
                👥
            </div>
            
            <div class="space-y-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#FF9521]/15 text-[#FF9521] border border-[#FF9521]/30">
                    Fase de Diseño y Planificación
                </span>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                    Módulo de Recursos Humanos
                </h2>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                Este panel concentrará la gestión de expedientes de personal, roles, incidencias laborales y nómina. La administración de cuentas de usuario operativas se realiza actualmente desde la sección de <strong class="text-slate-800 dark:text-slate-200">Personal</strong>.
            </p>

            <div class="pt-4">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                    ← Volver al Dashboard Principal
                </a>
            </div>
        </div>

    </div>
</x-tb-background>
</x-app-layout>