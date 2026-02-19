<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $soloPassword = ! in_array(strtolower((string) optional($user->role)->slug), ['admin', 'ceo'], true);

        return view('profile.edit', [
            'user' => $user,
            'soloPassword' => $soloPassword,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $esAdminOCeo = in_array(strtolower((string) optional($user->role)->slug), ['admin', 'ceo'], true);

        if ($esAdminOCeo) {
            $validated = $request->validate([
                'nombre' => ['required', 'string', 'max:255'],
                'segundo_nombre' => ['nullable', 'string', 'max:255'],
                'apellido_paterno' => ['required', 'string', 'max:255'],
                'apellido_materno' => ['nullable', 'string', 'max:255'],
                'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['nullable', 'confirmed', Password::defaults()],
                'foto_perfil' => ['nullable', 'image', 'max:20480'],
            ]);

            $user->nombre = $validated['nombre'];
            $user->segundo_nombre = $validated['segundo_nombre'] ?? null;
            $user->apellido_paterno = $validated['apellido_paterno'];
            $user->apellido_materno = $validated['apellido_materno'] ?? null;
            $user->fecha_nacimiento = $validated['fecha_nacimiento'] ?? null;
            $user->email = $validated['email'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            if ($request->hasFile('foto_perfil')) {
                $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
                $user->foto_perfil = $path;
            }

            $user->save();

            return back()->with('status', 'Perfil actualizado correctamente.');
        }

        $validated = $request->validate([
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fecha_nacimiento = $validated['fecha_nacimiento'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('foto_perfil')) {
            $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
            $user->foto_perfil = $path;
        }

        $user->save();

        return back()->with('status', 'Perfil actualizado correctamente.');
    }

    public function destroy(Request $request)
    {
        //
    }
}
