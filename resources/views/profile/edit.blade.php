<x-app-layout>
    {{-- HEADER IGUAL QUE LAS DEMÁS VISTAS --}}
    <x-slot name="header">
        <div class="-mx-4 sm:-mx-6 lg:-mx-8">
            <div
                class="px-4 sm:px-6 lg:px-8 py-3
                       bg-gradient-to-r 
                           from-slate-100/90 via-slate-200/95 to-slate-100/90
                       dark:from-slate-900/95 dark:via-slate-950/95 dark:to-slate-900/95
                       backdrop-blur-xl
                       border-b border-slate-200/70 dark:border-slate-800/80
                       shadow-md shadow-slate-900/40"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-lg sm:text-xl text-slate-900 dark:text-slate-50 leading-tight">
                                Perfil de usuario
                            </h2>

                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                       text-[0.65rem] font-medium tracking-wide
                                       bg-sky-500/10 text-sky-700
                                       dark:bg-sky-400/15 dark:text-sky-200
                                       border border-sky-500/25"
                            >
                                Cuenta · Configuración
                            </span>
                        </div>

                        <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Administra la información de tu cuenta, correo y contraseña.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- FONDO IGUAL QUE REGISTRO / INVENTARIO --}}
    <div class="relative py-10 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300
                dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 min-h-screen overflow-hidden">

        <div class="pointer-events-none absolute inset-0 
                    bg-white/10 dark:bg-white/5 
                    backdrop-blur-2xl">
        </div>

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto space-y-8">

                {{-- 1. INFORMACIÓN DE PERFIL --}}
                <div
                    class="rounded-2xl border border-slate-200/80 
                           bg-white/95 dark:bg-slate-900/95
                           dark:border-slate-700/80 shadow-lg shadow-slate-900/40"
                >


                    <div class="px-6 py-6">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- 2. CAMBIO DE CONTRASEÑA --}}
                <div
                    class="rounded-2xl border border-slate-200/80 
                           bg-white/95 dark:bg-slate-900/95
                           dark:border-slate-700/80 shadow-lg shadow-slate-900/40"
                >

                    <div class="px-6 py-6">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- 3. ELIMINAR CUENTA (SI LO USAS EN BREEZE) --}}
                <div
                    class="rounded-2xl border border-red-300/80 
                           bg-red-50/95 dark:bg-red-950/70
                           dark:border-red-700/80 shadow-lg shadow-red-900/40"
                >
                    <div class="px-6 py-5 border-b border-red-200 dark:border-red-800/80">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-50">
                            Eliminar Cuenta
                        </h3>
                        <p class="mt-1 text-sm text-red-700/90 dark:text-red-100/90">
                            Eliminacion permanente de tu cuenta.
                        </p>
                    </div>

                    <div class="px-6 py-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
