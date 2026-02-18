<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function roleSlug(?User $user = null): ?string
    {
        $u = $user ?: auth()->user();
        return optional($u->role)->slug;
    }

    private function isGlobal(?User $user = null): bool
    {
        $slug = $this->roleSlug($user);
        return in_array($slug, ['ceo', 'admin_sistema', 'sistemas'], true);
    }

    /**
     * Autoriza si el usuario autenticado puede gestionar al usuario objetivo.
     */
    private function authorizeUserScope(User $target): void
    {
        $auth = auth()->user();

        if (!$auth) {
            abort(403);
        }

        // Global roles: acceso total
        if ($this->isGlobal($auth)) {
            return;
        }

        // Puede editarse a sí mismo
        if ($auth->id === $target->id) {
            return;
        }

        // Gerente / Líder → solo mismo departamento
        if ($auth->departamento_id !== $target->departamento_id) {
            abort(403, 'No puedes gestionar usuarios fuera de tu departamento.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $auth = auth()->user();

        $query = User::query();

        if (!$this->isGlobal()) {
            $query->where('departamento_id', $auth->departamento_id);

            if ($this->roleSlug($auth) === 'lider') {
                $query->whereHas('role', fn($q) => $q->where('slug', 'tecnico'));
            }
        }

        $users = $query->orderBy('nombre')->get();

        return view('usuarios.index', compact('users'));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */



    public function edit(User $user)
    {
        $this->authorizeUserScope($user);

        $roles = Roles::orderBy('nombre')->get();

        return view('usuarios.edit', compact('user', 'roles'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $user)
    {
        $this->authorizeUserScope($user);

        $auth = auth()->user();
        $isGlobal = $this->isGlobal($auth);

        $rules = [
            'nombre'           => ['required', 'string', 'max:255'],
            'segundo_nombre'   => ['nullable', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'         => ['nullable', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'      => ['nullable', 'image', 'max:20480'],
        ];

        if ($isGlobal) {
            $rules['role_id'] = ['required', 'integer', 'exists:roles,id'];
        }

        $validated = $request->validate($rules);

        // Foto
        if ($request->hasFile('foto_perfil')) {

            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $validated['foto_perfil'] =
                $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        $payload = [
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'email'            => $validated['email'],
            'foto_perfil'      => $validated['foto_perfil'] ?? $user->foto_perfil,
        ];

        if ($isGlobal) {
            $payload['role_id'] = $validated['role_id'];
        }

        $user->update($payload);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', '¡Usuario actualizado exitosamente!');
    }
}
