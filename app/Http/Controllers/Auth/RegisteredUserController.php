<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    private function esGlobal(?string $slug): bool
    {
        return in_array(strtolower((string) $slug), ['ceo', 'admin', 'admin_sistema', 'sistemas'], true);
    }

    private function esAreaManager(?string $slug): bool
    {
        return in_array(strtolower((string) $slug), ['gerente', 'gerente_area', 'lider', 'lider_area', 'lider_de_area'], true);
    }

    private function allowedRoleSlugsForCreator(?string $creatorSlug): array
    {
        $creatorSlug = strtolower((string) $creatorSlug);

        if ($this->esGlobal($creatorSlug)) {
            return [];
        }

        // Regla: mismo nivel o menor.
        if (in_array($creatorSlug, ['gerente', 'gerente_area'], true)) {
            return ['gerente', 'gerente_area', 'lider', 'lider_area', 'lider_de_area', 'tecnico'];
        }

        if (in_array($creatorSlug, ['lider', 'lider_area', 'lider_de_area'], true)) {
            return ['lider', 'lider_area', 'lider_de_area', 'tecnico'];
        }

        return [];
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

    private function resolvePuestoIdForCreation(?int $departamentoId, int $roleId): ?int
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

    /**
     * Mostrar formulario de registro.
     */
    public function create()
    {
        if (! Auth::check()) {
            abort(403);
        }

        $auth = Auth::user();
        $slug = strtolower((string) optional($auth->role)->slug);

        if (! $this->esGlobal($slug) && ! $this->esAreaManager($slug)) {
            abort(403);
        }

        $isGlobalCreator = $this->esGlobal($slug);
        $allowedSlugs = $this->allowedRoleSlugsForCreator($slug);

        if (! $isGlobalCreator && empty($allowedSlugs)) {
            abort(403);
        }

        $roles = $isGlobalCreator
            ? Roles::orderBy('nombre')->get()
            : Roles::whereIn('slug', $allowedSlugs)->orderBy('nombre')->get();

        $departamentos = collect();
        $fixedDepartamento = null;

        if ($isGlobalCreator) {
            $departamentos = Departamento::query()->orderBy('nombre')->get(['id', 'nombre']);
        } else {
            $fixedDepartamento = Departamento::query()
                ->whereKey($auth->departamento_id)
                ->first(['id', 'nombre']);

            if (! $fixedDepartamento) {
                abort(403, 'Tu usuario no tiene un departamento valido asignado.');
            }
        }

        $puestoPreviewByDepartamento = $this->buildPuestoPreviewByDepartamento();
        $initialPuestoLabel = $fixedDepartamento
            ? ($puestoPreviewByDepartamento[(string) $fixedDepartamento->id] ?? 'Sin puesto configurado')
            : 'Sin puesto configurado';

        return view('auth.register', compact(
            'roles',
            'isGlobalCreator',
            'departamentos',
            'fixedDepartamento',
            'puestoPreviewByDepartamento',
            'initialPuestoLabel'
        ));
    }

    /**
     * Guardar nuevo usuario en la BD.
     */
    public function store(Request $request)
    {
        if (! Auth::check()) {
            abort(403);
        }

        $auth = Auth::user();
        $slug = strtolower((string) optional($auth->role)->slug);
        $isGlobalCreator = $this->esGlobal($slug);

        if (! $isGlobalCreator && ! $this->esAreaManager($slug)) {
            abort(403);
        }

        $allowedSlugs = $this->allowedRoleSlugsForCreator($slug);
        if (! $isGlobalCreator && empty($allowedSlugs)) {
            abort(403);
        }

        $rules = [
            'nombre'            => ['required', 'string', 'max:255'],
            'segundo_nombre'    => ['nullable', 'string', 'max:255'],
            'apellido_paterno'  => ['required', 'string', 'max:255'],
            'apellido_materno'  => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento'  => ['nullable', 'date', 'before_or_equal:today'],

            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'foto_perfil'       => ['nullable', 'image', 'max:20480'],

            // Global puede elegir; no-global se fuerza al del usuario activo.
            'departamento_id'   => $isGlobalCreator
                ? ['required', 'integer', 'exists:departamento,id']
                : ['nullable', 'integer'],
        ];

        if ($isGlobalCreator) {
            $rules['role_id'] = ['required', 'integer', 'exists:roles,id'];
        } else {
            $rolesPermitidos = Roles::whereIn('slug', $allowedSlugs)->pluck('id')->all();
            $rules['role_id'] = ['required', 'integer', 'in:' . implode(',', $rolesPermitidos)];
        }

        $validated = $request->validate($rules);

        if (! $isGlobalCreator) {
            if ($auth->departamento_id === null) {
                abort(403);
            }

            $validated['departamento_id'] = $auth->departamento_id;
        }

        $puestoId = $this->resolvePuestoIdForCreation(
            isset($validated['departamento_id']) ? (int) $validated['departamento_id'] : null,
            (int) $validated['role_id']
        );

        $fotoPath = null;
        if ($request->hasFile('foto_perfil')) {
            $fotoPath = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        User::create([
            'nombre'           => $validated['nombre'],
            'segundo_nombre'   => $validated['segundo_nombre'] ?? null,
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,

            'email'            => $validated['email'],
            'role_id'          => $validated['role_id'],
            'departamento_id'  => $validated['departamento_id'] ?? null,
            'puesto_id'        => $puestoId,

            'password'         => Hash::make($validated['password']),
            'foto_perfil'      => $fotoPath,
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado correctamente.');
    }
}
