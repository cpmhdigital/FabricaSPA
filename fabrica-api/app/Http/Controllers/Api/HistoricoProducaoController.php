<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producao\HistoricoProducao;
use App\Services\RealtimeService;

class HistoricoProducaoController extends Controller
{
    protected RealtimeService $realtime;

    public function __construct(RealtimeService $realtime)
    {
        $this->realtime = $realtime;
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'pedido_item_id' => 'required|integer|exists:pedido_item,id',
            'pedido_item_unidade_id' => 'required|integer|exists:pedido_item_unidade,id',
            'etapa_id' => 'required|integer|exists:etapa,id',
            'acao' => 'required|in:INICIO,PAUSA,FINALIZACAO,REPROVACAO,DECISAO',
            'tipo_decisao' => 'nullable|in:RETRABALHO,REFUGO',
            'etapa_destino_id' => 'nullable|exists:etapa,id',
            'observacao' => 'nullable|string',
        ]);

        $validated['usuario_id'] = $request->user()->id;
        $validated['data_hora'] = now();

        // Cria o histórico
        $historico = HistoricoProducao::create($validated);

        // Carrega relacionamentos essenciais
        $historico->load(['unidade.item.pedido', 'unidade.historicos']);

        // Pega o pedido completo
        $pedido = $historico->unidade->item->pedido;

        // Atualiza status e etapa atual de cada unidade
        foreach ($pedido->itens as $item) {
            foreach ($item->unidades as $unidade) {
                $ultimoHistorico = $unidade->historicos()->latest('data_hora')->first();

                if ($ultimoHistorico) {
                    $unidade->etapa_atual_id = $ultimoHistorico->etapa_id ?? $unidade->etapa_atual_id;
                    $unidade->etapa_atual_nome = $ultimoHistorico->etapa->nome ?? $unidade->etapa_atual_nome;

                    $unidade->status = match ($ultimoHistorico->acao) {
                        'INICIO' => 'em_andamento',
                        'PAUSA' => 'pausado',
                        'FINALIZACAO' => 'finalizado',
                        'REPROVACAO' => 'reprovado',
                        'DECISAO' => $ultimoHistorico->tipo_decisao === 'RETRABALHO' ? 'aguardando' : 'finalizado',
                        default => 'aguardando'
                    };
                }
            }
        }

        // 🔥 Dispara evento Ably
        $this->realtime->publicarAtualizacaoPedido($pedido);

        // 🔔 Se a ação foi REPROVACAO, envia evento especial para alertar no frontend
        if ($validated['acao'] === 'REPROVACAO') {
            $this->realtime->publicarReprovacaoPedido($pedido);
        }

        return response()->json([
            'message' => 'Histórico registrado com sucesso.',
            'data' => $historico->load(['usuario', 'etapa', 'etapaDestino', 'unidade'])
        ], 201);
    }


    // Mantém index e show como estavam
    public function index(Request $request)
    {
        $query = HistoricoProducao::with(['usuario', 'etapa', 'etapaDestino', 'unidade']);

        if ($request->has('pedido_item_unidade_id')) {
            $query->where('pedido_item_unidade_id', $request->pedido_item_unidade_id);
        }

        $historicos = $query->orderBy('data_hora', 'asc')->get();

        return response()->json($historicos);
    }

    public function show($unidadeId)
    {
        $historico = HistoricoProducao::with('usuario')
            ->where('pedido_item_unidade_id', $unidadeId)
            ->orderBy('data_hora', 'desc')
            ->get();

        if ($historico->isEmpty()) {
            return response()->json([], 200);
        }

        return response()->json($historico);
    }
}
