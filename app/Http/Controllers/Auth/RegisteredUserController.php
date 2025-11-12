<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Roles; // <-- 1. ¡IMPORTAMOS EL MODELO ROLES!
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     */
    public function create(): View
    {
        // 2. BUSCAMOS TODOS LOS ROLES
        $roles = Roles::all();
        
        // 3. SE LOS PASAMOS A LA VISTA
        return view('auth.register', [
            'roles' => $roles
        ]);
    }

    /**
     * Maneja la petición de registro.
     */
    public function store(Request $request): RedirectResponse
    {
        // 4. AÑADIMOS LA VALIDACIÓN PARA EL ROL
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'segundo_nombre' => ['nullable', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'integer', 'exists:roles,id'], // <-- REGLA NUEVA
        ]);

        // 5. AÑADIMOS EL ROLE_ID AL CREAR EL USUARIO
        $user = User::create([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id, // <-- CAMPO NUEVO
            'is_active' => true,
        ]);

        event(new Registered($user));

        // Como el admin creó el usuario, no iniciamos sesión como él.
        // Lo regresamos al dashboard con un mensaje de éxito.
        return redirect()->route('dashboard')->with('status', '¡Usuario creado exitosamente!');
    }
}