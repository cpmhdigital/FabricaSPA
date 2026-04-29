<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Doo\DooService;
use Illuminate\Http\JsonResponse;

class DooController extends Controller
{
    public function health(DooService $doo): JsonResponse
    {
        return response()->json($doo->health());
    }

    public function matrizes(DooService $doo): JsonResponse
    {
        $raw = $doo->matrizes();

        $normalizado = collect($raw)->map(fn ($m) => [
            'id' => $m['id'] ?? null,
            'codigo' => $m['codigo'] ?? ($m['code'] ?? null),
            'descricao' => $m['descricao'] ?? ($m['description'] ?? null),
        ])->values();

        return response()->json($normalizado);
    }

    public function syncMatrizes(DooService $doo): JsonResponse
    {
        // aqui você implementa a sincronização no seu DB
        // $doo->sincronizarMatrizes();

        return response()->json(['message' => 'Sync executado (implementar gravação no DB).']);
    }
}
