<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producao\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoParametroProducaoController extends Controller
{
    /**
     * Lista registros filtrando por pedido, unidade ou etapa.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'nullable|integer|exists:pedidos,id',
            'item_id' => 'nullable|integer|exists:itens,id',
            'pedido_item_unidade_id' => 'nullable|integer|exists:pedido_item_unidade,id',
            'etapa_id' => 'nullable|integer|exists:etapa,id',
        ]);

        $query = Parametro::with(['parametro', 'etapa', 'usuario', 'unidade'])
            ->when($validated['pedido_id'] ?? null, fn($q, $pid) => $q->where('pedido_id', $pid))
            ->when($validated['item_id'] ?? null, fn($q, $pid) => $q->where('item_id', $pid))
            ->when($validated['pedido_item_unidade_id'] ?? null, fn($q, $uid) => $q->where('pedido_item_unidade_id', $uid))
            ->when($validated['etapa_id'] ?? null, fn($q, $eid) => $q->where('etapa_id', $eid));

        return response()->json($query->get(), 200);
    }

    /**
     * Cria registros de parâmetros.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'item_id' => 'required|integer|exists:itens,id',
            'etapa_id' => 'required|integer|exists:etapa,id',
            'pedido_item_unidade_id' => 'required|integer|exists:pedido_item_unidade,id',

            'parametros' => 'required|array|min:1',
            'parametros.*.parametro_id' => 'required|integer|exists:etapa_parametros,id',
            'parametros.*.valor' => 'nullable|string|max:255',

            'usuario_id' => 'required|integer|exists:users,id',
        ]);

        $registros = [];

        foreach ($validated['parametros'] as $parametro) {
            $registros[] = Parametro::create([
                'pedido_id' => $validated['pedido_id'],
                'item_id' => $validated['item_id'],
                'etapa_id' => $validated['etapa_id'],
                'pedido_item_unidade_id' => $validated['pedido_item_unidade_id'],
                'parametro_id' => $parametro['parametro_id'],
                'valor' => $parametro['valor'] ?? null,
                'usuario_id' => $validated['usuario_id'],
            ]);
        }

        return response()->json([
            'message' => 'Parâmetros registrados com sucesso!',
            'data' => $registros,
        ], 201);
    }

    /**
     * Atualiza valor do parâmetro.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'valor' => 'nullable|string|max:255',
        ]);

        $registro = Parametro::findOrFail($id);

        $registro->update([
            'valor' => $validated['valor'] ?? $registro->valor,
        ]);

        return response()->json([
            'message' => 'Valor atualizado com sucesso!',
            'data' => $registro,
        ], 200);
    }

    /**
     * Remove registro.
     */
    public function destroy(int $id)
    {
        $registro = Parametro::findOrFail($id);
        $registro->delete();

        return response()->json([
            'message' => 'Registro excluído com sucesso.'
        ], 200);
    }
}
