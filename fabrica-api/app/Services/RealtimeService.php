<?php

namespace App\Services;

use Ably\AblyRest;
use App\Models\Pedido\Pedido;

class RealtimeService
{
    private AblyRest $ably;

    public function __construct()
    {
        $this->ably = new AblyRest([
            'key' => env('ABLY_KEY'), // chave completa do servidor
        ]);
    }

    /**
     * Envia uma atualização do pedido para o canal "kanban"
     */
    public function publicarAtualizacaoPedido(Pedido $pedido)
    {
        \Log::info("🔥 [ABLY] Iniciando envio de atualização...", [
            'pedido_id' => $pedido->id
        ]);

        $payload = $this->montarKanbanParaPedido($pedido);

        \Log::info("📦 [ABLY] Payload enviado:", $payload);

        try {
            $this->ably
                ->channel('kanban')
                ->publish('update', $payload);

            \Log::info("✅ [ABLY] Atualização enviada com sucesso!");
        } catch (\Exception $e) {
            \Log::error("❌ [ABLY] ERRO AO ENVIAR:", [
                'erro' => $e->getMessage()
            ]);
        }
    }

    public function publicarReprovacaoPedido($pedido)
    {
        $payload = $this->montarKanbanParaPedido($pedido);

        $itemReprovado = null;
        $unidadeReprovada = null;
        $etapaReprovada = null;
        $historicoReprovacao = null;

        foreach ($pedido->itens as $item) {
            foreach ($item->unidades as $u) {

                $ultimoHist = $u->historicos()->latest('data_hora')->first();

                if ($ultimoHist && $ultimoHist->acao === "REPROVACAO") {

                    $itemReprovado = $item;
                    $unidadeReprovada = $u;
                    $etapaReprovada = $ultimoHist->etapa->nome_etapa;
                    $historicoReprovacao = $ultimoHist;

                    break 2;
                }
            }
        }

        $resumido = [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,

            'item_id' => $itemReprovado->id,
            'item_codigo' => $itemReprovado->produto->codigo,
            'item_nome' => $itemReprovado->produto->descricao,

            'unidade_codigo' => $unidadeReprovada->unidade_codigo ?? null,

            'etapa_reprovada' => $etapaReprovada,

            'reprovado_por_id' => $historicoReprovacao->usuario->id,
            'reprovado_por_nome' => $historicoReprovacao->usuario->name,

            'observacao' => $historicoReprovacao->observacao,

            'data' => $historicoReprovacao->data_hora->format("d/m/Y H:i:s"),

            'timestamp' => now()->toDateTimeString()
        ];

        $this->ably->channel('kanban')->publish('pedido-reprovado', $resumido);
    }


    /**
     * Monta estrutura JSON esperada pelo Vue
     */
    private function montarKanbanParaPedido(Pedido $pedido): array
    {
        $itens = $pedido->itens->map(function ($item) {

            // Fluxo e primeira etapa
            $fluxo = $item->produto->fluxo;
            $primeiraEtapa = $fluxo->etapas->sortBy('pivot_ordem')->first();

            // Unidades
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
                'etapa_inicial_nome' => $primeiraEtapa?->nome_etapa,

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

    /**
     * Calcula a etapa atual de uma unidade
     */
    private function calcularEtapaAtual($fluxo, $ultimoHist): array
    {
        $etapas = $fluxo->etapas;

        // Nenhum histórico ainda → primeira etapa
        if (!$ultimoHist) {
            $primeira = $etapas->first();
            return [
                'etapa_id' => $primeira?->id,
                'etapa_nome' => $primeira?->nome_etapa,
                'status' => 'aguardando',
            ];
        }

        $acao = $ultimoHist->acao;
        $etapaAtualId = $ultimoHist->etapa_id;

        $index = $etapas->search(fn($e) => $e->id === $etapaAtualId);

        if ($acao === 'PAUSA') {
            return [
                'etapa_id' => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
                'status' => 'pausado',
            ];
        }

        if ($acao === 'INICIO') {
            return [
                'etapa_id' => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
                'status' => 'em_andamento',
            ];
        }

        if ($acao === 'FINALIZACAO') {

            $proxima = $etapas[$index + 1] ?? null;

            if ($proxima) {
                return [
                    'etapa_id'   => $proxima->id,
                    'etapa_nome' => $proxima->nome_etapa,
                    'status'     => 'aguardando',
                ];
            }

            // Terminou tudo
            return [
                'etapa_id' => null,
                'etapa_nome' => 'Finalizado',
                'status' => 'finalizado',
            ];
        }

        if ($acao === 'REPROVACAO') {
            return [
                'etapa_id' => $etapaAtualId,
                'etapa_nome' => $etapas[$index]->nome_etapa,
                'status' => 'reprovado',
            ];
        }


        // Default
        return [
            'etapa_id' => $etapaAtualId,
            'etapa_nome' => $etapas[$index]->nome_etapa ?? 'Etapa desconhecida',
            'status' => 'em_andamento',
        ];
    }
}
