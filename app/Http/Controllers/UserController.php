<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Roles; // <-- ¡Importante!
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index()
    {
        // 1. Busca todos los usuarios con su relación de 'role' cargada
        $usuarios = User::with('role')->get();

        // 2. Manda los usuarios a la vista
        return view('usuarios.index', [
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $user) // $user se inyecta automáticamente desde la URL
    {
        // 1. Busca todos los roles para el menú desplegable
        $roles = Roles::all();
        
        // 2. Muestra la vista 'edit.blade.php' y le pasa el usuario y los roles
        return view('usuarios.edit', [
            'usuario' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Actualiza el usuario en la base de datos.
     */
    public function update(Request $request, User $user)
    {
        // 1. Valida los datos que vienen del formulario
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id], // Único, ignorando a este usuario
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Opcional: solo si quieren cambiar la contraseña
        ]);

        // 2. Actualiza los datos del usuario
        $user->update([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        // 3. (Opcional) Si escribieron una contraseña, actualízala
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // 4. Redirige de vuelta a la tabla con un mensaje de éxito
        return redirect()->route('users.index')->with('status', '¡Usuario actualizado exitosamente!');
    }
}