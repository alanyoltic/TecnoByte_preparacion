<div
    class="fixed inset-y-0 left-0 z-30 h-screen bg-company-dark text-gray-200 transition-all duration-300 ease-in-out"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
>
    <div class="flex flex-col h-full">
        
        <div class="flex items-center h-16 px-4 py-4" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
            
            <a href="{{ route('dashboard') }}" class="text-white text-2xl font-bold" x-show="sidebarOpen" x-transition>
                TecnoByte
            </a>

            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="p-2 rounded-md text-gray-400 hover:text-gray-100 hover:bg-gray-700 focus:outline-none"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <nav class="mt-8 flex-1"> <a 
                class="flex items-center px-6 py-3 mt-4 text-gray-100 hover:bg-gray-700" 
                :class="{ 'justify-center': !sidebarOpen }"
                href="{{ route('dashboard') }}"
                title="Dashboard"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 15h.01M12 9h.01M9 15h.01M12 15h.01"></path></svg>
                <span class="mx-3" x-show="sidebarOpen" x-transition>Dashboard</span>
            </a>

            @if(in_array(auth()->user()->role?->slug, ['ceo', 'admin']))
                <x-dropdown align="right" width="64">
                    
                    <x-slot name="trigger">
                        <button 
                            class="flex items-center w-full px-6 py-3 mt-4 text-gray-100 hover:bg-gray-700" 
                            :class="{ 'justify-center': !sidebarOpen }"
                            title="Administrar Usuarios"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            
                            <span class="mx-3" x-show="sidebarOpen" x-transition>Usuarios</span>

                            <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20" x-show="sidebarOpen" x-transition>
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('register')">
                            Agregar Usuario
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('users.index')">
                            Administrar Usuarios
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            @endif

            </nav>

        <div class="border-t border-gray-700">
            <x-dropdown align="top" width="64">
                
                <x-slot name="trigger">
                    <button class="flex items-center w-full px-6 py-4 text-sm font-medium rounded-md text-gray-400 hover:text-gray-100 hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150"
                        :class="{ 'justify-center': !sidebarOpen }">
                        
                        <svg class="w-8 h-8 rounded-full" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 17a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
                        
                        <div class="ms-3 text-left" x-show="sidebarOpen" x-transition>
                            <div>{{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}</div>
                            <div class="text-xs font-medium text-gray-500">{{ Auth::user()->email }}</div>
                        </div>

                        <div class="ms-auto" x-show="sidebarOpen" x-transition>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"></path></svg>
                        </div>

                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" 
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

    </div>
</div>