<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Conecta\ConectaClient;
use App\Services\Conecta\PedidoMapper;
use Illuminate\Http\JsonResponse;
use Throwable;

class ConectaPedidosController extends Controller
{
    public function index(ConectaClient $client): JsonResponse
    {
        try {
            $pedidos = $client->fetchPedidos();

            $data = array_map(
                fn (array $p) => PedidoMapper::toDto($p),
                $pedidos
            );

            return response()->json([
                'count' => count($data),
                'data'  => $data,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error'   => 'Falha ao consultar Conecta',
                'message' => $e->getMessage(),
            ], 502);
        }
    }
}
