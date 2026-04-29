<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itens;
use App\Models\Fluxo;
use App\Services\ProdutoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItensController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $itensRaiz = Itens::with('fluxo')
            ->whereDoesntHave('pai')
            ->get();

        $itensComHierarquia = $itensRaiz->map(
            fn ($item) => Itens::carregarFilhosRecursivo($item, 0, 5)
        );

        return response()->json($itensComHierarquia);
    }

    /**
     * ✅ Lista somente itens pendentes (PENDENTE em tipo OU anvisa)
     * GET /api/itens/pendentes
     */
    public function pendentes(): JsonResponse
    {
        $pendentes = Itens::with('fluxo')
            ->where(function ($q) {
                $q->whereRaw("UPPER(TRIM(tipo)) = 'PENDENTE'")
                  ->orWhereRaw("UPPER(TRIM(anvisa)) = 'PENDENTE'");
            })
            ->orderByDesc('id')
            ->get();

        return response()->json($pendentes);
    }

    public function indexTipo(Request $request): JsonResponse
    {
        $query = Itens::query();

        if ($request->has('tipo') && $request->tipo) {
            $tipo = trim(strtolower($request->tipo));
            $query->whereRaw('LOWER(TRIM(tipo)) = ?', [$tipo]);
        }

        return response()->json($query->get());
    }

    public function verificarCodigo(Request $request): JsonResponse
    {
        $codigo = $request->get('codigo');

        $existeProduto = Itens::where('codigo', $codigo)
            ->where('tipo', 'produto')
            ->exists();

        $existeComponente = Itens::where('codigo', $codigo)
            ->whereIn('tipo', ['componente', 'parafuso'])
            ->exists();

        return response()->json([
            'produto' => $existeProduto,
            'componente' => $existeComponente,
        ]);
    }

    public function buscar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string',
            'tipoItem' => 'required|string',
            'acao' => 'nullable|string|in:novo,pesquisa',
        ]);

        $codigo = $validated['codigo'];
        $tipoItem = $validated['tipoItem'];

        $item = Itens::where('codigo', $codigo)->first();

        if ($tipoItem === 'produto') {
            return response()->json(['exists' => (bool) $item]);
        }

        if ($item) {
            $itemComFilhos = Itens::carregarFilhosRecursivo($item, 0, 5);
            return response()->json([
                'exists' => true,
                'data' => $itemComFilhos
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function show($id): JsonResponse
    {
        $item = Itens::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item não encontrado'], 404);
        }

        $itemComFilhos = Itens::carregarFilhosRecursivo($item, 0, 5);
        return response()->json($itemComFilhos);
    }

    public function store(Request $request, ProdutoService $service): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:itens,codigo',
            'descricao' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'anvisa' => 'nullable|string|max:100',
            'fluxo_id' => 'nullable|integer|exists:fluxo,id',
            'itens' => 'nullable|array',
        ]);

        if (!isset($validated['itens']) || !is_array($validated['itens'])) {
            $validated['itens'] = [];
        }

        $produto = $service->criarProdutoComItens($validated);

        return response()->json([
            'message' => 'Produto criado com sucesso',
            'id' => $produto->id
        ]);
    }

    public function filtros(): JsonResponse
    {
        $materiasPrimas = Itens::where('tipo', 'materia_prima')
            ->select('id', 'descricao')
            ->distinct()
            ->orderBy('descricao')
            ->get();

        $fluxo = Fluxo::whereIn('id', Itens::whereNotNull('fluxo_id')->pluck('fluxo_id'))
            ->get()
            ->map(fn($f) => ['id' => $f->id, 'nome_fluxo' => $f->nome_fluxo])
            ->sortBy('nome_fluxo')
            ->values();

        return response()->json([
            'materias_primas' => $materiasPrimas,
            'fluxo' => $fluxo,
        ]);
    }

    public function update(Request $request, Itens $item): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => 'sometimes|required|string|max:50|unique:itens,codigo,' . $item->id,
            'descricao' => 'sometimes|required|string|max:255',
            'anvisa' => 'nullable|string|max:100',
            'tipo' => 'sometimes|required|string|max:50',
            'fluxo_id' => 'nullable|integer|exists:fluxo,id'
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Item atualizado com sucesso',
            'data' => $item
        ]);
    }

    public function destroy(Itens $item): JsonResponse
    {
        $item->delete();
        return response()->json(['message' => 'Item excluído com sucesso']);
    }

    /**
     *  Busca simples para modal
     */
    public function buscaSimples(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $itens = Itens::with('fluxo')
            ->where(function ($w) use ($q) {
                $w->where('descricao', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%");
            })
            // evita puxar pendentes pra compor
            ->whereRaw("UPPER(TRIM(tipo)) <> 'PENDENTE'")
            ->orderBy('descricao')
            ->limit(30)
            ->get(['id', 'descricao', 'codigo', 'tipo', 'fluxo_id']);

        return response()->json($itens);
    }

    /**
     * Finalizar cadastro do pendente e salvar composição na pivot itens_composicao
     */
    public function finalizar(Request $request, $id): JsonResponse
    {
        $item = Itens::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item não encontrado'], 404);
        }

        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:itens,codigo,' . $item->id,
            'descricao' => 'required|string|max:255',
            'anvisa' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:50',
            'fluxo_id' => 'nullable|integer|exists:fluxo,id',

            'componentes' => 'nullable|array',
            'componentes.*.item_id' => 'required_with:componentes|integer|exists:itens,id',
            'componentes.*.quantidade' => 'required_with:componentes|numeric|min:1',
        ]);

        return DB::transaction(function () use ($item, $validated) {
            // 1) Atualiza cadastro principal
            $item->codigo = $validated['codigo'];
            $item->descricao = $validated['descricao'];

            $item->anvisa = $validated['anvisa'] ?? null;
            if (trim(strtoupper((string)$item->anvisa)) === 'PENDENTE') {
                $item->anvisa = null;
            }

            $item->tipo = $validated['tipo'];
            $item->fluxo_id = $validated['fluxo_id'] ?? null;
            $item->save();

            // 2) Composição via pivot itens_composicao
            $componentes = $validated['componentes'] ?? [];

            // Monta sync com campos extras do pivot
            $sync = [];
            foreach ($componentes as $c) {
                $sync[$c['item_id']] = [
                    'tipo' => 'componente',
                    'quantidade' => $c['quantidade'], // remova se sua pivot não tem quantidade
                ];
            }

            // filhos(): belongsToMany usando itens_composicao (item_pai_id / item_filho_id)
            $item->filhos()->sync($sync);

            return response()->json([
                'message' => 'Cadastro finalizado com sucesso',
                'data' => $item->fresh(['fluxo'])
            ]);
        });
    }
}
