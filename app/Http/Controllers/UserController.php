<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    private function roleSlug(?User $user = null): string
    {
        $u = $user ?: auth()->user();
        return strtolower((string) optional($u->role)->slug);
    }

    private function isGlobal(?User $user = null): bool
    {
        $slug = $this->roleSlug($user);
        return in_array($slug, ['ceo', 'admin', 'admin_sistema', 'sistemas'], true);
    }

    private function isCeo(?User $user = null): bool
    {
        return $this->roleSlug($user) === 'ceo';
    }

    private function allowedRoleSlugsForEditor(?User $user = null): array
    {
        $slug = $this->roleSlug($user);

        if ($this->isGlobal($user)) {
            return [];
        }

        if (in_array($slug, ['gerente', 'gerente_area'], true)) {
            return ['gerente', 'gerente_area', 'lider', 'lider_area', 'lider_de_area', 'tecnico'];
        }

        if (in_array($slug, ['lider', 'lider_area', 'lider_de_area'], true)) {
            return ['lider', 'lider_area', 'lider_de_area', 'tecnico'];
        }

        return [];
    }

    private function authorizeUserScope(User $target): void
    {
        $auth = auth()->user();

        if (! $auth) {
            abort(403);
        }

        if ($this->isGlobal($auth)) {
            return;
        }

        $allowedSlugs = $this->allowedRoleSlugsForEditor($auth);
        if (empty($allowedSlugs)) {
            abort(403, 'No tienes permisos para gestionar usuarios.');
        }

        if ((int) $auth->departamento_id !== (int) $target->departamento_id) {
            abort(403, 'No puedes gestionar usuarios fuera de tu departamento.');
        }

        $targetSlug = $this->roleSlug($target);
        if (! in_array($targetSlug, $allowedSlugs, true)) {
            abort(403, 'No puedes gestionar un rol superior a tu nivel.');
        }
    }

    private function buildPuestoPreviewByDepartamento(): array
    {
        $rows = DB::table('departamento as d')
            ->leftJoin('departamento_puestos as dp', function ($join) {
                $join->on('dp.departamento_id', '=', 'd.id')
                    ->where('dp.activo', '=', 1);
            })
            ->leftJoin('puestos as p', 'p.id', '=', 'dp.puesto_id')
            ->select('d.id as departamento_id', DB::raw('MIN(p.nombre) as puesto_nombre'))
            ->groupBy('d.id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row->departamento_id] = $row->puesto_nombre ?: 'Sin puesto configurado';
        }

        return $map;
    }

    private function resolvePuestoIdForUser(?int $departamentoId, int $roleId): ?int
    {
        $roleSlug = strtolower((string) Roles::whereKey($roleId)->value('slug'));

        if ($roleSlug === 'ceo') {
            $direccionId = Puesto::query()->whereRaw('UPPER(clave) = ?', ['DIRECCION'])->value('id');
            if ($direccionId) {
                return (int) $direccionId;
            }
        }

        if (! $departamentoId) {
            return null;
        }

        $puestoId = DB::table('departamento_puestos')
            ->where('departamento_id', $departamentoId)
            ->where('activo', 1)
            ->orderBy('puesto_id')
            ->value('puesto_id');

        return $puestoId ? (int) $puestoId : null;
    }

    public function index()
    {
        $auth = auth()->user();

        $query = User::query();

        if (! $this->isGlobal($auth)) {
            $allowedSlugs = $this->allowedRoleSlugsForEditor($auth);
            if (empty($allowedSlugs)) {
                abort(403, 'No tienes permisos para gestionar usuarios.');
            }

            $query->where('departamento_id', $auth->departamento_id);
            $query->whereHas('role', fn ($q) => $q->whereIn('slug', $allowedSlugs));
        }

        $users = $query->orderBy('nombre')->get();

        return view('usuarios.index', compact('users'));
    }

    public function edit(User $user)
    {
        $this->authorizeUserScope($user);

        $auth = auth()->user();

        if ($this->isGlobal($auth)) {
            $roles = Roles::orderBy('nombre')->get();
        } else {
            $allowedSlugs = $this->allowedRoleSlugsForEditor($auth);
            if (empty($allowedSlugs)) {
                abort(403, 'No tienes permisos para editar roles.');
            }

            $roles = Roles::whereIn('slug', $allowedSlugs)
                ->orderBy('nombre')
                ->get();
        }

        $canEditDepartment = $this->isCeo($auth);
        $departamentos = $canEditDepartment
            ? Departamento::query()->orderBy('nombre')->get(['id', 'nombre'])
            : collect();

        $puestoPreviewByDepartamento = $canEditDepartment
            ? $this->buildPuestoPreviewByDepartamento()
            : [];

        return view('usuarios.edit', compact(
            'user',
            'roles',
            'canEditDepartment',
            'departamentos',
            'puestoPreviewByDepartamento'
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserScope($user);

        $auth = auth()->user();
        $isGlobal = $this->isGlobal($auth);
        $allowedSlugs = $this->allowedRoleSlugsForEditor($auth);

        $rules = [
            'nombre'           => ['required', 'string', 'max:255'],
            'segundo_nombre'   => ['nullable', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'         => ['nullable', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'      => ['nullable', 'image', 'max:20480'],
        ];

        if ($isGlobal) {
            $rules['role_id'] = ['required', 'integer', 'exists:roles,id'];
        } else {
            if (empty($allowedSlugs)) {
                abort(403, 'No tienes permisos para editar roles.');
            }

            $allowedRoleIds = Roles::whereIn('slug', $allowedSlugs)->pluck('id')->all();
            $rules['role_id'] = ['required', 'integer', 'in:' . implode(',', $allowedRoleIds)];
        }

        $canEditDepartment = $this->isCeo($auth);
        if ($canEditDepartment) {
            $rules['departamento_id'] = ['required', 'integer', 'exists:departamento,id'];
            $rules['confirmar_riesgo_departamento'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $validated['foto_perfil'] = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        $payload = [
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'email'            => $validated['email'],
            'foto_perfil'      => $validated['foto_perfil'] ?? $user->foto_perfil,
            'role_id'          => $validated['role_id'],
        ];

        $nuevoDepartamentoId = (int) ($validated['departamento_id'] ?? $user->departamento_id);
        $departamentoCambio = $canEditDepartment && ($nuevoDepartamentoId !== (int) $user->departamento_id);

        if ($departamentoCambio && ! $request->boolean('confirmar_riesgo_departamento')) {
            return back()
                ->withErrors([
                    'departamento_id' => 'Debes confirmar el riesgo antes de cambiar el departamento.',
                ])
                ->withInput();
        }

        if ($canEditDepartment) {
            $payload['departamento_id'] = $nuevoDepartamentoId;
        }

        $departamentoParaPuesto = isset($payload['departamento_id'])
            ? (int) $payload['departamento_id']
            : (int) $user->departamento_id;
        $payload['puesto_id'] = $this->resolvePuestoIdForUser($departamentoParaPuesto, (int) $payload['role_id']);

        $user->update($payload);

        if (! empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario actualizado exitosamente!');
    }
}
