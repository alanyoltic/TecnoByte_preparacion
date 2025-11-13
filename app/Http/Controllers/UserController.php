<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- Importa el modelo User
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index()
    {
        // 1. Busca todos los usuarios (puedes paginarlo después)
        // 2. 'with('role')' carga la información del rol para ser más eficientes
        $usuarios = User::with('role')->get();

        // 3. Manda los usuarios a la nueva vista que vamos a crear
        return view('usuarios.index', [
            'usuarios' => $usuarios
        ]);
    }
}