<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido\Pedido;
use Illuminate\Http\JsonResponse;

class KanbanController extends Controller
{
    public function index(): JsonResponse
    {
        $pedidos = Pedido::select([
            'id',
            'numero_pedido',
            'doutor',
            'paciente',
            'data_pedido',
            'data_aprovacao_pcp',
            'created_at'
        ])
            ->with([
                'itens:id,pedido_id,produto_id,quantidade',

                'itens.produto:id,codigo,descricao,tipo,fluxo_id',

                'itens.produto.fluxo:id,nome_fluxo',

                // precisa do pivot ordem!!
                'itens.produto.fluxo.etapas'
                => function ($q) {
                    $q->select('etapa.id', 'nome_etapa')
                        ->orderBy('fluxo_etapa.ordem');
                },

                'itens.unidades:id,pedido_item_id,unidade_codigo,created_at',

                'itens.unidades.ultimoHistorico' => function ($q) {
                    $q->select(
                        'id',
                        'pedido_item_unidade_id',
                        'acao',
                        'etapa_id',
                        'usuario_id',
                        'data_hora',
                        'created_at'
                    )->with([
                        'usuario:id,name',
                        'etapa:id,nome_etapa'
                    ]);
                },
            ])
            ->get();

        // monta o kanban
        $kanban = $pedidos->map(function ($pedido) {

            $itens = $pedido->itens->map(function ($item) {

                $fluxo = $item->produto->fluxo;

                // --- ETAPA INICIAL DO ITEM (primeira do fluxo) ---
                $primeiraEtapa = $fluxo->etapas->sortBy('pivot_ordem')->first();

                $unidades = $item->unidades->map(function ($u) use ($fluxo) {

                    $etapa = $this->calcularEtapaAtual(
                        fluxo: $fluxo,
                        ultimoHist: $u->ultimoHistorico
                    );

                    return [
                        'id' => $u->id,
                        'codigo' => $u->unidade_codigo,
                        'etapa_atual_id' => $etapa['etapa_id'],
                        'etapa_atual_nome' => $etapa['etapa_nome'],
                        'status' => $etapa['status'],
                    ];
                });

                return [
                    'item_id' => $item->id,
                    'nome' => $item->produto->descricao,
                    'codigo' => $item->produto->codigo,

                    // Agora exibe a PRIMEIRA etapa do fluxo do produto corretamente:
                    'etapa_inicial_id'   => $primeiraEtapa?->id,
                    'etapa_inicial_nome' => $primeiraEtapa?->nome_completo ?? $primeiraEtapa?->nome,

                    // E continua mantendo a etapa atual da primeira unidade:
                    'etapa_atual_codigo' => $unidades->first()['codigo'] ?? null,

                    'etapa_atual_nome' => $unidades->first()['etapa_atual_nome'] ?? null,


                    'unidades' => $unidades,
                ];
            });

            return [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'status' => 'em_andamento',
                'progresso' => 0,
                'itens' => $itens,
            ];
        });


        return response()->json($kanban);
    }

    private function calcularEtapaAtual($fluxo, $ultimoHist)
    {
        $etapas = $fluxo->etapas;

        // -- 1) nunca começou → aguardando a primeira etapa
        if (!$ultimoHist) {
            $primeira = $etapas->first();
            return [
                'etapa_id' => $primeira?->id,
                'etapa_nome' => $primeira?->nome_etapa,
                'status' => 'aguardando'
            ];
        }

        $acao = $ultimoHist->acao;
        $etapaAtualId = $ultimoHist->etapa_id;

        // acha o índice da etapa
        $index = $etapas->search(fn($e) => $e->id === $etapaAtualId);


        // -- 2) PAUSADO
        if ($acao === 'PAUSA') {
            return [
                'etapa_id'  => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
                'status'    => 'pausado'
            ];
        }

        // -- 3) RETOMADO = volta a ficar em andamento
        if ($acao === 'RETOMADO') {
            return [
                'etapa_id'  => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
                'status'    => 'em_andamento'
            ];
        }

        if ($acao === 'REPROVACAO') {
            return [
                'etapa_id'  => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
                'status'    => 'reprovado'
            ];
        }

        // -- 4) FINALIZACAO = vai para a próxima etapa
        if ($acao === 'FINALIZACAO') {

            $proxima = $etapas[$index + 1] ?? null;

            if ($proxima) {
                return [
                    'etapa_id'   => $proxima->id,
                    'etapa_nome' => $proxima->nome_etapa,
                    'status'     => 'aguardando'
                ];
            }

            // não tem mais etapas → finalizado
            return [
                'etapa_id'  => null,
                'etapa_nome' => 'Finalizado',
                'status'    => 'finalizado'
            ];
        }


        // -- 5) padrão: está trabalhando na etapa atual
        return [
            'etapa_id'  => $etapaAtualId,
            'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
            'status'    => 'em_andamento'
        ];
    }


    private function montarKanbanParaPedido($pedido)
    {
        $itens = $pedido->itens->map(function ($item) {

            $fluxo = $item->produto->fluxo;
            $primeiraEtapa = $fluxo->etapas->sortBy('pivot_ordem')->first();

            $unidades = $item->unidades->map(function ($u) use ($fluxo) {
                $etapa = $this->calcularEtapaAtual($fluxo, $u->ultimoHistorico);

                return [
                    'id' => $u->id,
                    'codigo' => $u->unidade_codigo,
                    'etapa_atual_id' => $etapa['etapa_id'],
                    'etapa_atual_nome' => $etapa['etapa_nome'],
                    'status' => $etapa['status'],
                ];
            });

            return [
                'item_id' => $item->id,
                'nome' => $item->produto->descricao,
                'codigo' => $item->produto->codigo,
                'etapa_inicial_id' => $primeiraEtapa?->id,
                'etapa_inicial_nome' => $primeiraEtapa?->nome ?? $primeiraEtapa?->nome_etapa,
                'etapa_atual_codigo' => $unidades->first()['codigo'] ?? null,
                'etapa_atual_nome' => $unidades->first()['etapa_atual_nome'] ?? null,
                'unidades' => $unidades,
            ];
        });

        return [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'status' => 'em_andamento',
            'progresso' => 0,
            'itens' => $itens,
        ];
    }
}
