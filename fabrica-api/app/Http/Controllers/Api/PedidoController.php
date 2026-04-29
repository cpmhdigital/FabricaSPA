<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\PedidoItem;
use App\Models\Pedido\PedidoItemComponente;
use App\Models\Pedido\PedidoItemUnidade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PedidoItemUnidadeService;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    private PedidoItemUnidadeService $unidadeService;

    public function __construct(PedidoItemUnidadeService $unidadeService)
    {
        $this->unidadeService = $unidadeService;
    }

    /* =====================================================
       LISTAR PEDIDOS
    ===================================================== */
    /*   public function index(Request $request): JsonResponse
    {
        $status = $request->get('status');

        $pedidos = Pedido::with('pedidoItens.produto')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->get();

        return response()->json($pedidos);
    } */
    public function index(Request $request): JsonResponse
    {
        $status = $request->get('status');
        $modalidade = $request->get('modalidade'); // customlife|atm|ancorfix
        $tipo = $request->get('tipo'); // nacional|internacional

        $pedidos = Pedido::with(['pedidoItens.produto'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($tipo, function ($q) use ($tipo) {
                $q->whereRaw('LOWER(tipo) = ?', [strtolower($tipo)]);
            })
            ->when($modalidade, function ($q) use ($modalidade) {
                $needle = strtolower($modalidade);

                // map do filtro (param) -> termo real na descricao
                $map = [
                    'customlife' => 'custom',
                    'ancorfix'   => 'ancor',
                    'atm'        => 'atm',
                ];

                $search = $map[$needle] ?? $needle;

                $q->whereHas('pedidoItens.produto', function ($qq) use ($search) {
                    $qq->whereRaw('LOWER(descricao) LIKE ?', ['%' . $search . '%']);
                });
            })

            ->orderByDesc('id')
            ->get();

        return response()->json($pedidos);
    }

    /* =====================================================
       CRIAR PEDIDO (POST /pedidos)
    ===================================================== */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero_pedido' => 'required|string|max:50|unique:pedidos,numero_pedido',
            'lote'          => 'nullable|string|max:50',
            'doutor'        => 'nullable|string|max:100',
            'paciente'      => 'nullable|string|max:100',
            'tipo'          => 'nullable|string|max:50',
            'taxa_extra'    => 'boolean',
            'data_pedido'   => 'nullable|date',
        ]);

        $validated['data_pedido'] = $validated['data_pedido']
            ?? now()->toDateString();

        $pedido = Pedido::create($validated);

        return response()->json($pedido, 201);
    }


    /* =====================================================
       ADICIONAR / ATUALIZAR ITENS DO PEDIDO
       POST /pedidos/{pedido}/itens
    ===================================================== */
    public function storeItens(Request $request, Pedido $pedido): JsonResponse
    {
        Log::info('📥 REQUEST RAW (ANTES DA VALIDACAO)', $request->all());

        $validated = $request->validate([
            'itens' => 'required|array|min:1',

            'itens.*.produto_id' => 'required|integer|exists:itens,id',
            'itens.*.quantidade' => 'required|integer|min:1',

            'itens.*.componentes' => 'array',
            'itens.*.componentes.*.componente_id' => 'required|integer|exists:itens,id',
            'itens.*.componentes.*.quantidade' => 'required|integer|min:1',

            'itens.*.extras' => 'array',
            'itens.*.extras.*.item_id' => 'required|integer|exists:itens,id',
            'itens.*.extras.*.quantidade' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $pedido) {

            foreach ($validated['itens'] as $itemData) {

                /* ================= ITEM DO PEDIDO ================= */

                $pedidoItem = PedidoItem::updateOrCreate(
                    [
                        'pedido_id'  => $pedido->id,
                        'produto_id' => $itemData['produto_id'],
                    ],
                    [
                        'quantidade' => $itemData['quantidade'],
                    ]
                );


                /* ================= UNIDADES ================= */

                $this->unidadeService->sincronizarUnidades($pedidoItem);
                $unidadesPai = $pedidoItem->unidades()->get();

                /* ================= COMPONENTES NORMAIS ================= */

                foreach ($itemData['componentes'] ?? [] as $comp) {

                    $pic = PedidoItemComponente::updateOrCreate(
                        [
                            'pedido_item_id' => $pedidoItem->id,
                            'componente_id'  => $comp['componente_id'],
                            'tipo' => 'normal',
                        ],
                        [
                            'quantidade' => $comp['quantidade'],
                        ]
                    );

                    foreach ($unidadesPai as $u) {
                        $pic->unidades()->firstOrCreate([
                            'unidade_codigo' => $u->unidade_codigo,
                        ]);
                    }
                }

                /* ================= LIMPEZA COMPONENTES REMOVIDOS ================= */

                $idsComponentes = collect($itemData['componentes'] ?? [])
                    ->pluck('componente_id')
                    ->toArray();

                PedidoItemComponente::where('pedido_item_id', $pedidoItem->id)
                    ->where('tipo', 'normal')
                    ->whereNotIn('componente_id', $idsComponentes)
                    ->delete();

                /* ================= EXTRAS ================= */

                foreach ($itemData['extras'] ?? [] as $extra) {

                    $pic = PedidoItemComponente::updateOrCreate(
                        [
                            'pedido_item_id' => $pedidoItem->id,
                            'componente_id'  => $extra['item_id'],
                            'tipo' => 'extra',
                        ],
                        [
                            'quantidade' => $extra['quantidade'],
                        ]
                    );

                    foreach ($unidadesPai as $u) {
                        $pic->unidades()->firstOrCreate([
                            'unidade_codigo' => $u->unidade_codigo,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Itens adicionados/atualizados com sucesso',
        ], 201);
    }
    /* =====================================================
   DETALHAR PEDIDO
   GET /pedidos/{id}
===================================================== */
    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with([
            'pedidoItens.produto.fluxo.etapas.maquinas',
            'pedidoItens.produto.fluxo.etapas.itRevs',
            'pedidoItens.produto.fluxo.etapas.parametros',
            'pedidoItens.produto.fluxo.etapas.checklistsPre',
            'pedidoItens.produto.fluxo.etapas.checklistsPos',

            'pedidoItens.componentes.componente.fluxo.etapas.maquinas',
            'pedidoItens.componentes.componente.fluxo.etapas.itRevs',
            'pedidoItens.componentes.componente.fluxo.etapas.parametros',
            'pedidoItens.componentes.componente.fluxo.etapas.checklistsPre',
            'pedidoItens.componentes.componente.fluxo.etapas.checklistsPos',

            'pedidoItens.unidades.historicos',
            'responsavelPcp',
        ])->findOrFail($id);

        return response()->json($pedido);
    }

    /* =====================================================
       ATUALIZAR PEDIDO
    ===================================================== */

    public function update(Request $request, Pedido $pedido): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        $pedido->update([
            'status' => 'aprovado',
            'responsavel_pcp_id' => Auth::id(),
            'data_aprovacao_pcp' => now(),
        ]);

        return response()->json(['message' => 'Pedido atualizado com sucesso']);
    }

    /* =====================================================
       REMOVER PEDIDO
    ===================================================== */
    public function destroy(Pedido $pedido): JsonResponse
    {
        $pedido->delete();

        return response()->json([
            'message' => 'Pedido removido com sucesso',
        ]);
    }
}
