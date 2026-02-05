<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Helpers (UNIFICADOS)
     */
    private function roleSlug(?User $user = null): ?string
    {
        $u = $user ?: auth()->user();
        return optional($u->role)->slug;
    }

    private function esGlobal(?User $user = null): bool
    {
        $slug = $this->roleSlug($user);
        return in_array($slug, ['ceo', 'admin_sistema', 'sistemas'], true);
    }

    private function enforcePuedeGestionarUsuario(User $target): void
    {
        $auth = auth()->user();

        // Globales: todo
        if ($this->esGlobal($auth)) {
            return;
        }

        // Siempre puedo editarme a mí mismo
        if ((int) $auth->id === (int) $target->id) {
            return;
        }

        // Gerente/Líder: solo dentro del mismo departamento
        if ($auth->departamento_id === null || $target->departamento_id !== $auth->departamento_id) {
            abort(403);
        }
    }


    /**
     * Lista de usuarios
     */
    public function index()
    {
        $auth = auth()->user();
        $authSlug = optional($auth->role)->slug;

        $query = User::query();

        if (! $this->esGlobal()) {
            $query->where('departamento_id', $auth->departamento_id);

            if ($authSlug === 'lider') {
                // lider solo ve tecnicos
                $query->whereHas('role', fn($q) => $q->where('slug', 'tecnico'));
            }
        }

        $usuarios = $query->orderBy('nombre')->get();

        return view('usuarios.index', compact('usuarios'));
    }

public function edit(User $user)
{
    $this->enforceScopeUsuarios($user);
    return view('sistema.usuarios.edit', compact('user'));
}


    /**
     * Guardar cambios del usuario
     */
    public function update(Request $request, User $user)
    {


        $this->enforceScopeUsuarios($user);

        $esGlobal = $this->esGlobal();
        // Bloquear cross-departamento para gerente/lider
        $this->enforcePuedeGestionarUsuario($user);


        $auth = auth()->user();
        $esGlobal = $this->esGlobal($auth);

        // Validación base
        $rules = [
            'nombre'            => ['required', 'string', 'max:255'],
            'segundo_nombre'    => ['nullable', 'string', 'max:255'],
            'apellido_paterno'  => ['required', 'string', 'max:255'],
            'apellido_materno'  => ['nullable', 'string', 'max:255'],
            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'          => ['nullable', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'       => ['nullable', 'image', 'max:20480'],
        ];

        // Solo roles globales pueden cambiar role_id (evita escalación)
        if ($esGlobal) {
            $rules['role_id'] = ['required', 'integer', 'exists:roles,id'];
        }

        $validated = $request->validate($rules);

        /**
         * Foto
         */
        if ($request->hasFile('foto_perfil')) {

            // borrar foto anterior si existe
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
            $validated['foto_perfil'] = $path;
        }

        /**
         * Payload update
         */
        $payload = [
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'email'            => $validated['email'],
            'foto_perfil'      => $validated['foto_perfil'] ?? $user->foto_perfil,
        ];

        if ($esGlobal) {
            $payload['role_id'] = $validated['role_id'];
        }

        $user->update($payload);

        /**
         * Password (si viene)
         */
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        return redirect()
            ->route('users.index')
            ->with('status', '¡Usuario actualizado exitosamente!');
    }
}
