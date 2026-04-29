<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producao\RegistroParada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RegistroParadaController extends Controller
{
    /**
     * Listar paradas de um pedido (ou geral)
     */
    public function index(Request $request, $pedidoId = null): JsonResponse
    {
        $query = RegistroParada::with(['pedido', 'pedidoItem', 'pedidoItemUnidade', 'etapa', 'usuario'])
            ->orderByDesc('created_at');

        if ($pedidoId) {
            $query->where('pedido_id', $pedidoId);
        } elseif ($request->has('pedido_id')) {
            $query->where('pedido_id', $request->get('pedido_id'));
        }

        // filtros opcionais: etapa, motivo, usuario
        if ($request->filled('etapa_id')) {
            $query->where('etapa_id', $request->get('etapa_id'));
        }
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->get('motivo'));
        }

        $dados = $query->paginate(25);

        return response()->json($dados);
    }

    /**
     * Registrar uma parada
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'item_id' => 'required|integer|exists:itens,id',
            'pedido_item_unidade_id' => 'required|integer|exists:pedido_item_unidade,id',
            'etapa_id' => 'nullable|integer|exists:etapa,id',
            'motivo' => [
                'required',
                Rule::in(['almoco', 'lanche', 'fim_expediente', 'outro'])
            ],
            'observacao' => 'nullable|string'
        ]);

        $usuarioId = Auth::id() ?? $request->get('usuario_id');

        $registro = RegistroParada::create([
            'pedido_id' => $request->pedido_id,
            'item_id' => $request->item_id,
            'pedido_item_unidade_id' => $request->pedido_item_unidade_id,
            'etapa_id' => $request->etapa_id,
            'usuario_id' => $usuarioId,
            'motivo' => $request->motivo,
            'observacao' => $request->observacao
        ]);

        return response()->json([
            'message' => 'Registro de parada criado com sucesso.',
            'data' => $registro->load(['pedidoItem', 'pedidoItemUnidade', 'usuario'])
        ], 201);
    }
}
