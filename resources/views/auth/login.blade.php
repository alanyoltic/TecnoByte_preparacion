<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="relative w-full max-w-xl mx-auto">

            {{-- LUCECITAS / GLOW DE FONDO DETRÁS DEL CARD --}}
            <div
                class="pointer-events-none absolute -top-40 left-1/2 -translate-x-1/2
                       w-[26rem] h-[26rem] rounded-full
                       bg-sky-500/25 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-40 -right-20
                       w-[24rem] h-[24rem] rounded-full
                       bg-indigo-600/25 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-44 -left-24
                       w-[22rem] h-[22rem] rounded-full
                       bg-orange-500/20 blur-3xl"
            ></div>

            {{-- CARD PRINCIPAL --}}
            <div
                class="relative z-10 bg-slate-950/85
                       border border-slate-800/80
                       rounded-3xl
                       shadow-[0_25px_80px_rgba(15,23,42,0.85)]
                       px-10 py-10
                       text-slate-100"
            >
                {{-- LOGO --}}
                <div class="flex justify-center mb-6">
                    <img
                        src="{{ asset('images/logo-tecnobyte.png') }}"
                        alt="TecnoByte"
                        class="h-16 md:h-20 object-contain"
                    >
                </div>

                {{-- TÍTULO --}}
                <div class="text-center mb-6">
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                        Servicios TecnoByte
                    </h1>
                    <p class="mt-2 text-sm md:text-base text-slate-400">
                        Inicia sesión para acceder a preparación e inventario.
                    </p>
                </div>

                {{-- ESTADO DE SESIÓN / ERRORES (Laravel Breeze / Fortify) --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Correo electrónico')" class="text-sm font-medium text-slate-200" />

                        <x-text-input
                            id="email"
                            class="block mt-1 w-full
                                   bg-slate-900/70 border border-slate-700
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-400/70
                                   text-sm md:text-base"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                        />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" :value="__('Contraseña')" class="text-sm font-medium text-slate-200" />

                        <x-text-input
                            id="password"
                            class="block mt-1 w-full
                                   bg-slate-900/70 border border-slate-700
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-400/70
                                   text-sm md:text-base"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Recordarme + ¿Olvidaste tu contraseña? --}}
                    <div class="flex items-center justify-between text-xs md:text-sm text-slate-400">
                        <label for="remember_me" class="inline-flex items-center gap-2">
                            <input id="remember_me" type="checkbox"
                                   class="rounded border-slate-600 bg-slate-900/70 text-orange-500
                                          focus:ring-orange-400/70">
                            <span>{{ __('Recordarme') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                class="text-xs md:text-sm text-orange-300 hover:text-orange-200 hover:underline"
                                href="{{ route('password.request') }}"
                            >
                                {{ __('¿Olvidaste tu contraseña?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center
                                   px-4 py-2.5 md:py-3
                                   bg-[#FF9521]
                                   text-sm md:text-base font-semibold text-slate-950
                                   rounded-2xl
                                   shadow-[0_15px_40px_rgba(255,149,33,0.55)]
                                   hover:shadow-[0_18px_50px_rgba(255,149,33,0.75)]
                                   hover:bg-[#ffa63e]
                                   focus:outline-none focus:ring-2 focus:ring-offset-2
                                   focus:ring-offset-slate-950 focus:ring-[#FF9521]
                                   transition-all duration-200"
                        >
                            {{ __('Iniciar sesión') }}
                        </button>
                    </div>
                </form>

                {{-- PIE DE PÁGINA --}}
                <p class="mt-6 text-center text-[11px] md:text-xs text-slate-500">
                    © {{ now()->year }} Servicios TecnoByte. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
