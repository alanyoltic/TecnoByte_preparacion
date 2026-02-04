<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Roles; // <-- ¡Importante!
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index()
    {
            $query = \App\Models\User::query();

            $user = auth()->user();
            $slug = optional($user->role)->slug;

            // CEO ve todo
            if ($slug !== 'ceo') {
                // por ahora: zona = departamento
                $query->where('departamento_id', $user->departamento_id);
            }

            $users = $query->get();

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
        // (Opcional extra) Sólo permitir que admin / ceo editen usuarios:
        // if (!in_array(auth()->user()->role?->slug, ['admin', 'ceo'])) {
        //     abort(403);
        // }

        // 1. Valida los datos que vienen del formulario
        $validated = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'segundo_nombre'    => ['nullable', 'string', 'max:255'],
            'apellido_paterno'  => ['required', 'string', 'max:255'],
            'apellido_materno'  => ['nullable', 'string', 'max:255'],
            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role_id'           => ['required', 'integer', 'exists:roles,id'],
            'password'          => ['nullable', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'       => ['nullable', 'image', 'max:20480'], 
        ]);

        // 2. Si viene una nueva foto, la guardamos
        if ($request->hasFile('foto_perfil')) {

            // Si el usuario ya tenía una foto, la borramos del storage
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            // Guardamos la nueva foto en storage/app/public/fotos_perfil
            $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');

            // La agregamos al arreglo validado
            $validated['foto_perfil'] = $path;
        }

        // 3. Actualiza los datos básicos del usuario
        $user->update([
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'email'            => $validated['email'],
            'role_id'          => $validated['role_id'],
            // foto_perfil se agrega sólo si existe en $validated
            'foto_perfil'      => $validated['foto_perfil'] ?? $user->foto_perfil,
        ]);

        // 4. (Opcional) Si escribieron una contraseña, actualízala
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        // 5. Redirige de vuelta a la tabla con un mensaje de éxito
        return redirect()->route('users.index')->with('status', '¡Usuario actualizado exitosamente!');
    }
}
