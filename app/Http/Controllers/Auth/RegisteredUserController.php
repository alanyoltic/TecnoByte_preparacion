<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    private function esGlobal(?string $slug): bool
    {
        return in_array($slug, ['ceo', 'admin_sistema', 'sistemas'], true);
    }

    private function esAreaManager(?string $slug): bool
    {
        return in_array($slug, ['gerente', 'lider'], true);
    }


    private function authSlug(): ?string
{
    return optional(auth()->user()->role)->slug;
}


private function enforceScopeUsuarios(User $target): void
{
    $auth = auth()->user();
    $authSlug = optional($auth->role)->slug;

    // Globales: todo
    if ($this->esGlobal()) return;

    // Siempre puede editarse a sí mismo (si quieres quitar esto, dímelo)
    if ((int)$auth->id === (int)$target->id) return;

    // Debe ser mismo departamento
    if ($auth->departamento_id === null || (int)$target->departamento_id !== (int)$auth->departamento_id) {
        abort(403);
    }

    // Regla fuerte: lider SOLO puede administrar tecnicos (y opcional usuario)
    if ($authSlug === 'lider') {
        $targetSlug = optional($target->role)->slug;

        if ($targetSlug !== 'tecnico') {
            abort(403);
        }
    }
}


    private function allowedRoleSlugsForCreator(?string $creatorSlug): array
    {
        // Globales: sin restricción (se maneja aparte)
        if ($this->esGlobal($creatorSlug)) {
            return [];
        }

        // Regla solicitada:
        // gerente: puede crear lider/tecnico/usuario
        if ($creatorSlug === 'gerente') {
            return ['lider', 'tecnico', 'usuario'];
        }

        // lider: solo tecnico/usuario
        return ['tecnico', 'usuario'];
    }

    /**
     * Mostrar formulario de registro.
     */
    public function create()
    {
        if (! Auth::check()) abort(403);

        $auth = Auth::user();
        $slug = optional($auth->role)->slug;

        if (! $this->esGlobal($slug) && ! $this->esAreaManager($slug)) {
            abort(403);
        }

        if ($this->esGlobal($slug)) {
            $roles = Roles::orderBy('nombre')->get();
        } else {
            $allowedSlugs = $this->allowedRoleSlugsForCreator($slug);
            $roles = Roles::whereIn('slug', $allowedSlugs)->orderBy('nombre')->get();
        }

        return view('auth.register', compact('roles'));
    }

    /**
     * Guardar nuevo usuario en la BD.
     */
    public function store(Request $request)
    {
        if (! Auth::check()) abort(403);

        $auth = Auth::user();
        $slug = optional($auth->role)->slug;

        if (! $this->esGlobal($slug) && ! $this->esAreaManager($slug)) {
            abort(403);
        }

        // Validación base
        $rules = [
            'nombre'            => ['required', 'string', 'max:255'],
            'segundo_nombre'    => ['nullable', 'string', 'max:255'],
            'apellido_paterno'  => ['required', 'string', 'max:255'],
            'apellido_materno'  => ['nullable', 'string', 'max:255'],

            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'       => ['nullable', 'image', 'max:20480'],

            // si el form NO lo manda, no pasa nada (lo forzamos abajo)
            'departamento_id'   => ['nullable', 'integer'],
        ];

        // role_id:
        if ($this->esGlobal($slug)) {
            $rules['role_id'] = ['required', 'integer', 'exists:roles,id'];
        } else {
            $allowedSlugs = $this->allowedRoleSlugsForCreator($slug);
            $rolesPermitidos = Roles::whereIn('slug', $allowedSlugs)->pluck('id')->all();

            $rules['role_id'] = ['required', 'integer', 'in:' . implode(',', $rolesPermitidos)];
        }

        $validated = $request->validate($rules);

        // Forzar departamento para gerente/líder (siempre su mismo depto)
        if (! $this->esGlobal($slug)) {
            if ($auth->departamento_id === null) abort(403);
            $validated['departamento_id'] = $auth->departamento_id;
        }

        // Foto
        $fotoPath = null;
        if ($request->hasFile('foto_perfil')) {
            $fotoPath = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        User::create([
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,

            'email'            => $validated['email'],
            'role_id'          => $validated['role_id'],
            'departamento_id'  => $validated['departamento_id'] ?? null,

            'password'         => Hash::make($validated['password']),
            'foto_perfil'      => $fotoPath,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', '¡Usuario creado correctamente!');
    }
}
