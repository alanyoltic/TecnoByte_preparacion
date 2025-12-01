<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar formulario de registro (solo admin / ceo).
     */
    public function create()
    {
        // Solo permitir a admin y ceo entrar a esta pantalla
        if (!Auth::check() || !in_array(Auth::user()->role?->slug, ['admin', 'ceo'])) {
            abort(403);
        }

        // Roles para el select del formulario
        $roles = Roles::all();

        return view('auth.register', compact('roles'));
    }

    /**
     * Guardar nuevo usuario en la BD.
     */
    public function store(Request $request)
    {
        // Solo permitir a admin y ceo crear usuarios
        if (!Auth::check() || !in_array(Auth::user()->role?->slug, ['admin', 'ceo'])) {
            abort(403);
        }

        // Validación de los datos
        $validated = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'segundo_nombre'    => ['nullable', 'string', 'max:255'],
            'apellido_paterno'  => ['required', 'string', 'max:255'],
            'apellido_materno'  => ['nullable', 'string', 'max:255'],

            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role_id'           => ['required', 'integer', 'exists:roles,id'],

            'password'          => ['required', 'confirmed', Rules\Password::defaults()],

            // Foto de perfil opcional
            'foto_perfil'       => ['nullable', 'image', 'max:2048'], // 2 MB
        ]);

        // Manejar foto si se envió
        $fotoPath = null;
        if ($request->hasFile('foto_perfil')) {
            // Se guarda en storage/app/public/fotos_perfil
            $fotoPath = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        // Crear usuario con los datos validados
        $user = User::create([
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,

            'email'            => $validated['email'],
            'role_id'          => $validated['role_id'],

            'password'         => Hash::make($validated['password']),
            'foto_perfil'      => $fotoPath,
        ]);

        // IMPORTANTE:
        // No hacemos Auth::login($user) para NO cerrar la sesión del admin/ceo actual

        return redirect()
            ->route('users.index')
            ->with('status', '¡Usuario creado correctamente!');
    }
}
