<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <h2 class="text-2xl font-bold text-center text-gray-800">Crear Nuevo Usuario</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="segundo_nombre" value="Segundo Nombre (Opcional)" />
                <x-text-input id="segundo_nombre" class="block mt-1 w-full" type="text" name="segundo_nombre" :value="old('segundo_nombre')" />
                <x-input-error :messages="$errors->get('segundo_nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="apellido_paterno" value="Apellido Paterno" />
                <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="apellido_materno" value="Apellido Materno (Opcional)" />
                <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" />
                <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
            </div>
        </div>
        
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="role_id" value="Asignar Rol" />
            
            <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Seleccionar un rol --</option>
                
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->nombre }}
                    </option>
                @endforeach
            </select>
            
            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
        </div>
        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-4">
                Crear Usuario
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>