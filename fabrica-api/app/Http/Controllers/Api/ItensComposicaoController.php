<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Itens;
use App\Models\ItensComposicao;
use Illuminate\Http\JsonResponse;

class ItensComposicaoController extends Controller
{
    public function index(): JsonResponse
    {
        $composicoes = ItensComposicao::with(['itemPai', 'itemFilho'])->get();
        return response()->json($composicoes);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'item_pai_id' => 'required|integer|exists:itens,id',
            'item_filho_id' => 'required|integer|exists:itens,id',
            'tipo' => 'required|string',
        ]);

        $exists = ItensComposicao::where('item_pai_id', $request->item_pai_id)
            ->where('item_filho_id', $request->item_filho_id)
            ->where('tipo', $request->tipo)
            ->exists();

        $composicao = ItensComposicao::firstOrCreate(
            [
                'item_pai_id' => $request->item_pai_id,
                'item_filho_id' => $request->item_filho_id,
                'tipo' => $request->tipo,
            ]
        );


        return response()->json([
            'message' => $exists ? 'Composição já existia.' : 'Composição criada com sucesso!',
            'composicao' => $composicao,
        ]);
    }

    public function buscar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string',
            'tipoItem' => 'required|string',
        ]);

        $codigo = $validated['codigo'];
        $tipoItem = $validated['tipoItem'];

        $item = Itens::where('codigo', $codigo)
            ->where('tipo', $tipoItem)
            ->first();


        if (!$item) {
            return response()->json(['exists' => false]);
        }

        // Recuperar filhos via ItensComposicao
        $filhos = ItensComposicao::with('itemFilho')
            ->where('item_pai_id', $item->id)
            ->get()
            ->pluck('itemFilho');

        return response()->json([
            'exists' => true,
            'data' => [
                'pai' => $item,
                'filhos' => $filhos,
            ],
        ]);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'termo' => 'required|string|min:1',
        ]);

        $termo = $validated['termo'];

        $resultados = Itens::where('codigo', 'like', "%{$termo}%")
            ->orWhere('descricao', 'like', "%{$termo}%")
            ->limit(10)
            ->get();

        return response()->json($resultados);
    }


    public function show($id): JsonResponse
    {
        $composicao = ItensComposicao::with(['itemPai', 'itemFilho'])->findOrFail($id);
        return response()->json($composicao);
    }

    public function destroy($id): JsonResponse
    {
        $composicao = ItensComposicao::findOrFail($id);
        $composicao->delete();
        return response()->json(['message' => 'Composição removida com sucesso!']);
    }
}
