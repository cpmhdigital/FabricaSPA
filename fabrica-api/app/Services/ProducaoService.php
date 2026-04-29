<?php

namespace App\Services;

use App\Models\Pedido\Pedido;
use Carbon\Carbon;

class ProducaoService
{
    /**
     * Retorna o progresso completo de um pedido
     */
    public function gerarProgressoPedido(int $pedidoId)
    {
        $pedido = Pedido::with([

            // carrega o fluxo do produto e as etapas ordenadas pelo pivot fluxo_etapa.ordem
            'itens.produto.fluxo' => function ($q) {
                $q->with(['etapas' => function ($e) {
                    $e->orderBy('fluxo_etapa.ordem');
                }]);
            },

            // carrega unidades + históricos ordenados
            'itens.unidades.historicos' => function ($h) {
                $h->orderBy('data_hora');
            },

        ])->findOrFail($pedidoId);

        return [
            'pedido_id'     => $pedido->id,
            'status_pedido' => $this->statusPedido($pedido),
            'data_inicio'   => $this->dataInicio($pedido),
            'data_prevista' => $this->dataPrevista($pedido),
            'data_entrega'  => $this->dataEntrega($pedido),

            'progresso'     => $this->progressoPedido($pedido),
            'itens'         => $this->mapearItens($pedido)
        ];
    }


    /* ============================================================
        CÁLCULO DE DATAS
    ============================================================ */

    private function adicionarDiasUteis($data, $dias)
    {
        $data = Carbon::parse($data);

        while ($dias > 0) {
            $data->addDay();

            // pular finais de semana
            if ($data->isWeekend()) {
                continue;
            }

            $dias--;
        }

        return $data;
    }

    private function dataInicio($pedido)
    {
        $primeiroInicio = $pedido->itens
            ->flatMap(fn($i) => $i->unidades)
            ->flatMap(fn($u) => $u->historicos)
            ->filter(fn($h) => $h->acao === 'INICIO')
            ->sortBy('data_hora')
            ->first();

        return $primeiroInicio?->data_hora;
    }
    private function dataPrevista($pedido)
    {
        $produtoPrincipal = $pedido->itens->first()->produto;
        if (!$produtoPrincipal || !$produtoPrincipal->fluxo) {
            return null;
        }

        $fluxo = $produtoPrincipal->fluxo;

        $dias = $pedido->taxa_extra
            ? ($fluxo->tempo_estimado_dias_acelerado ?? $fluxo->tempo_estimado_dias)
            : $fluxo->tempo_estimado_dias;

        if (!$pedido->data_aprovacao_pcp) {
            return null;
        }

        return $this->adicionarDiasUteis($pedido->data_aprovacao_pcp, $dias)
            ->format('Y-m-d H:i:s');
    }

    private function dataEntrega($pedido)
    {
        $inicioReal = $this->dataInicio($pedido);

        if (!$inicioReal) {
            return null;
        }

        $produtoPrincipal = $pedido->itens->first()->produto;
        if (!$produtoPrincipal || !$produtoPrincipal->fluxo) {
            return null;
        }

        $fluxo = $produtoPrincipal->fluxo;

        $dias = $pedido->taxa_extra
            ? ($fluxo->tempo_estimado_dias_acelerado ?? $fluxo->tempo_estimado_dias)
            : $fluxo->tempo_estimado_dias;

        return $this->adicionarDiasUteis($inicioReal, $dias)
            ->format('Y-m-d H:i:s');
    }


    /* ============================================================
        STATUS DO PEDIDO / ITENS
    ============================================================ */

    private function statusPedido($pedido)
    {
        // prioridade: se algum item estiver bloqueado -> pedido bloqueado
        $temBloqueado = $pedido->itens->some(fn($it) => $this->statusItem($it) === 'bloqueado');
        if ($temBloqueado) return 'bloqueado';

        $percent = $this->progressoPedido($pedido);

        if ($percent === 0) return 'pendente';
        if ($percent === 100) return 'finalizado';

        return 'em_andamento';
    }

    private function statusItem($item)
    {
        // examina status das unidades
        $unitStatuses = $item->unidades->map(fn($u) => $this->statusUnidade($u));

        if ($unitStatuses->contains('bloqueado')) return 'bloqueado';
        if ($unitStatuses->contains('produzindo')) return 'produzindo';
        if ($unitStatuses->every(fn($s) => $s === 'finalizado')) return 'finalizado';
        if ($unitStatuses->every(fn($s) => $s === 'pendente')) return 'pendente';

        return 'em_andamento';
    }

    /* ============================================================
        PROGRESSO: PEDIDO, ITEM E UNIDADE
    ============================================================ */

    private function progressoPedido($pedido)
    {
        $total = 0;
        $count = 0;

        foreach ($pedido->itens as $item) {
            $total += $this->progressoItem($item);
            $count++;
        }

        return $count > 0 ? (int) floor($total / $count) : 0;
    }

    private function progressoItem($item)
    {
        $fluxo = $item->produto->fluxo;

        if (!$fluxo || $fluxo->etapas->count() == 0) {
            return 0;
        }

        $totalEtapas = $fluxo->etapas->count();
        $unidadesCount = max(1, $item->unidades->count());
        $etapasConcluidas = 0;

        foreach ($item->unidades as $unidade) {
            foreach ($fluxo->etapas as $etapa) {
                $hist = $unidade->historicos
                    ->where('etapa_id', $etapa->id)
                    ->sortBy('data_hora')
                    ->last();

                if ($hist?->acao === 'FINALIZACAO') {
                    $etapasConcluidas++;
                }
            }
        }

        // média considerando total de etapas * quantidade de unidades
        return $unidadesCount > 0 ? (int) floor($etapasConcluidas / ($totalEtapas * $unidadesCount) * 100) : 0;
    }

    private function progressoUnidade($unidade)
    {
        if (!$unidade->historicos->count()) {
            return 0;
        }

        $ultima = $unidade->historicos->sortBy('data_hora')->last();

        // se reprovação, fica bloqueado (0%)
        if ($ultima->acao === 'REPROVACAO') return 0;

        // se finalização da última etapa -> 100%
        // NOTE: para saber se é a última etapa precisamos do fluxo, por isso esse método é usado somente quando for necessário.
        if ($ultima->acao === 'FINALIZACAO') {
            return 100;
        }

        // INICIO / PAUSA / DECISAO -> estamos produzindo (percentual depende das etapas concluídas)
        return null; // quando usado no map, calculamos pelo número de etapas concluídas
    }

    /* ============================================================
        DESCOBRIR ETAPA ATUAL / STATUS UNIDADE
    ============================================================ */

    private function etapaAtual($unidade)
    {
        if (!$unidade->historicos->count()) {
            return null;
        }

        $historico = $unidade->historicos->sortBy('data_hora')->last();

        if ($historico->acao === 'REPROVACAO') {
            return null;
        }

        return $historico->etapa_id;
    }

    /**
     * Retorna um status textual da unidade baseado na última ação
     * (usado para determinar status do item)
     */
    private function statusUnidade($unidade)
    {
        $last = $unidade->historicos->sortBy('data_hora')->last();

        if (!$last) return 'pendente';

        if ($last->acao === 'REPROVACAO') return 'bloqueado';

        if ($last->acao === 'FINALIZACAO') {
            // se finalizou a última etapa do fluxo => finalizado
            $fluxo = $unidade->pedido_item?->produto?->fluxo ?? null; // fallback
            if ($fluxo && $fluxo->etapas->count()) {
                $etapas = $fluxo->etapas->sortBy('pivot.ordem')->values();
                $ultimoId = $etapas->last()->id ?? null;
                if ($last->etapa_id == $ultimoId) {
                    return 'finalizado';
                }
            }
            // caso contrário, consideramos produzindo (foi finalizada etapa porém tem próximas)
            return 'produzindo';
        }

        // INICIO, PAUSA, DECISAO e outros => produzindo
        return 'produzindo';
    }

    /* ============================================================
        MAPEAR ITENS E UNIDADES
    ============================================================ */

    private function detectarEtapasFluxo($fluxo, $unidade)
    {
        if (!$fluxo || $fluxo->etapas->count() == 0) {
            return [
                'anterior' => null,
                'atual'    => null,
                'proxima'  => null,
            ];
        }

        // Ordenar pelo pivot.ordem (garantido no eager load, mas reforço aqui)
        $etapas = $fluxo->etapas->sortBy('pivot.ordem')->values();

        // Última ação registrada para essa unidade
        $historico = $unidade->historicos->sortBy('data_hora')->last();

        if (!$historico) {
            return [
                'anterior' => null,
                'atual'    => null,
                'proxima'  => $etapas->get(0)?->id ?? null,
            ];
        }

        $etapaAtualId = $historico->etapa_id;

        // encontrar índice da etapa atual no fluxo
        $idx = $etapas->search(fn($e) => $e->id == $etapaAtualId);

        // se não encontrou (ex.: histórico de etapa não pertence ao fluxo atual), retorna primeira etapa como próxima
        if ($idx === false) {
            return [
                'anterior' => null,
                'atual'    => null,
                'proxima'  => $etapas->get(0)?->id ?? null,
            ];
        }

        return [
            'anterior' => $idx > 0 ? $etapas[$idx - 1]->id : null,
            'atual'    => $etapas[$idx]->id ?? null,
            'proxima'  => $etapas->get($idx + 1)?->id ?? null,
        ];
    }

    /*   private function mapearItens($pedido)
    {
        return $pedido->itens->map(function ($item) {

            $fluxo = $item->produto->fluxo;
            $totalEtapas = $fluxo?->etapas->count() ?? 0;

            // coletar status das unidades para deduzir status do item
            $unidades = $item->unidades->map(function ($u) use ($fluxo, $totalEtapas, $item) {

                $historicos = $u->historicos->sortBy('data_hora');

                // conta quantas etapas desse fluxo já foram finalizadas (último histórico por etapa)
                $etapasConcluidas = 0;
                if ($fluxo) {
                    foreach ($fluxo->etapas as $etapa) {
                        $histEtapa = $historicos
                            ->where('etapa_id', $etapa->id)
                            ->sortBy('data_hora')
                            ->last();

                        if ($histEtapa?->acao === 'FINALIZACAO') {
                            $etapasConcluidas++;
                        }
                    }
                }

                $etapasFluxo = $this->detectarEtapasFluxo($fluxo, $u);

                $lastHist = $historicos->last();

                // acao_atual = última ação registrada
                $acao_atual = $lastHist?->acao ?? null;

                // status humano da unidade (produzindo, bloqueado, finalizado, pendente)
                $statusUnidade = $this->statusUnidade($u);

                // progresso da unidade calculado por etapas concluídas
                $progressoUnidade = $totalEtapas ? (int) floor(($etapasConcluidas / $totalEtapas) * 100) : 0;
                // se a última ação foi FINALIZACAO e foi a última etapa -> 100%
                if ($lastHist?->acao === 'FINALIZACAO' && $fluxo) {
                    $etapasOrdenadas = $fluxo->etapas->sortBy('pivot.ordem')->values();
                    $ultimoEtapaId = $etapasOrdenadas->last()->id ?? null;
                    if ($lastHist->etapa_id == $ultimoEtapaId) {
                        $progressoUnidade = 100;
                    }
                }

                return [
                    'id'                 => $u->id,
                    'codigo'             => $u->unidade_codigo,
                    'etapas'             => $etapasFluxo,
                    'etapa_atual'        => $etapasFluxo['atual'],
                    'etapa_anterior'     => $etapasFluxo['anterior'],
                    'proxima_etapa'      => $etapasFluxo['proxima'],
                    'acao_atual'         => $acao_atual,
                    'progresso'          => $progressoUnidade,
                    'status'             => $statusUnidade,
                    'decisao'            => $lastHist?->tipo_decisao,
                    'total_etapas'       => $totalEtapas,
                    'etapas_concluidas'  => $etapasConcluidas,
                ];
            });

            // deduz status do item a partir das unidades
            $statuses = $unidades->pluck('status')->unique()->values()->all();

            $itemStatus = 'pendente';
            if (in_array('bloqueado', $statuses)) {
                $itemStatus = 'bloqueado';
            } elseif (in_array('produzindo', $statuses)) {
                $itemStatus = 'produzindo';
            } elseif (count($statuses) === 1 && $statuses[0] === 'finalizado') {
                $itemStatus = 'finalizado';
            } elseif (count($statuses) === 1 && $statuses[0] === 'pendente') {
                $itemStatus = 'pendente';
            } else {
                $itemStatus = 'em_andamento';
            }

            return [
                'item_id'        => $item->id,
                'nome'           => $item->produto->descricao ?? null,
                // inclui id do fluxo e nome do fluxo para uso externo (datas, etc)
                'fluxo_id'       => $fluxo->id ?? null,
                'fluxo_nome'     => $fluxo->nome ?? null,
                'progresso'      => $this->progressoItem($item),
                'status'         => $itemStatus,
                'unidades'       => $unidades,
            ];
        })->values();
    } */
    private function mapearItens($pedido)
    {
        return $pedido->itens->map(function ($item) {

            $fluxo = $item->produto->fluxo;
            $totalEtapas = $fluxo?->etapas->count() ?? 0;

            return [
                'item_id'       => $item->id,
                'nome'          => $item->produto->nome,
                'fluxo_id'      => $fluxo?->id,
                'fluxo_nome'    => $fluxo?->nome,
                'progresso'     => $this->progressoItem($item),
                'status'        => $this->statusItem($item),

                'unidades' => $item->unidades->map(
                    fn($u) =>
                    $this->mapearUnidade($u, $fluxo, $totalEtapas)
                )
            ];
        })->values();
    }

    private function mapearUnidade($unidade, $fluxo, $totalEtapas)
    {
        $historicos = $unidade->historicos->sortBy('data_hora');

        // etapas concluídas
        $etapasConcluidas = 0;
        if ($fluxo) {
            foreach ($fluxo->etapas as $etapa) {
                $hist = $historicos
                    ->where('etapa_id', $etapa->id)
                    ->sortBy('data_hora')
                    ->last();

                if ($hist?->acao === 'FINALIZACAO') {
                    $etapasConcluidas++;
                }
            }
        }

        $etapasFluxo = $this->detectarEtapasFluxo($fluxo, $unidade);
        $lastHist = $historicos->last();

        return [
            'id'                => $unidade->id,
            'codigo'            => $unidade->codigo,
            'etapas'            => $etapasFluxo,
            'etapa_atual'       => $etapasFluxo['atual'],
            'etapa_anterior'    => $etapasFluxo['anterior'],
            'proxima_etapa'     => $etapasFluxo['proxima'],

            'acao_atual'        => $lastHist?->acao,
            'decisao'           => $lastHist?->tipo_decisao,
            'status'            => $this->statusUnidade($unidade),

            'total_etapas'      => $totalEtapas,
            'etapas_concluidas' => $etapasConcluidas,

            'progresso'         => $totalEtapas > 0
                ? floor(($etapasConcluidas / $totalEtapas) * 100)
                : 0,
        ];
    }



    /* ============================================================
        UTILITÁRIOS
    ============================================================ */

    private function calcularEtapasConcluidas($item)
    {
        $fluxo = $item->produto->fluxo;
        if (!$fluxo) return 0;

        $concluidas = 0;

        foreach ($item->unidades as $unidade) {
            $historicos = $unidade->historicos->sortBy('data_hora');

            foreach ($fluxo->etapas as $etapa) {
                $ultimaAcao = $historicos->where('etapa_id', $etapa->id)->sortBy('data_hora')->last()?->acao;
                if ($ultimaAcao === 'FINALIZACAO') {
                    $concluidas++;
                }
            }
        }

        return $concluidas;
    }

    private function descobrirEtapaMaisAvancada($item)
    {
        return $item->unidades
            ->map(fn($u) => $this->etapaAtual($u))
            ->filter()
            ->max();
    }
}
