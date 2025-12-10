<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoteController extends Controller
{
    /**
     * Mostrar la pantalla para registrar un lote.
     */
        public function registrar()
        {
            return view('lotes.registrarlote');
        }

}
