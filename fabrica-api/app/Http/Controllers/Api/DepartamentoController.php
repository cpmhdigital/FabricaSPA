<?php

namespace App\Http\Controllers\Api;

use App\Models\Pedido;
use App\Http\Controllers\Controller;
use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function index()
    {
        $departamento = Departamento::all(['id', 'nome']);
        return response()->json(['departamento' => $departamento]);
    }
}
