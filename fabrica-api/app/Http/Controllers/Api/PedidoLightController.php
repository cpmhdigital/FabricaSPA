<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido\Pedido;
use Illuminate\Http\JsonResponse;

class PedidoLightController extends Controller
{
    public function show($id): JsonResponse
    {
        $pedido = Pedido::select([
            'id',
            'numero_pedido',
            'doutor',
            'paciente',
            'data_pedido',
            'data_aprovacao_pcp',
            'created_at'
        ])
            ->with([
                // itens do pedido
                'itens:id,pedido_id,produto_id,quantidade',

                // produto do item (usa tabela itens)
                'itens.produto:id,codigo,descricao,tipo,fluxo_id',

                // fluxo do produto
                'itens.produto.fluxo:id,nome_fluxo',

                // etapas do fluxo
                'itens.produto.fluxo.etapas:id,nome_etapa',

                // unidades do item
                'itens.unidades:id,pedido_item_id,unidade_codigo,created_at',

                // último histórico da unidade CALCULADO CORRETAMENTE
                'itens.unidades.ultimoHistorico' => function ($q) {
                    $q->select(
                        'id',
                        'pedido_item_unidade_id',
                        'acao',
                        'etapa_id',
                        'usuario_id',
                        'data_hora',
                        'created_at'
                    )
                    ->with([
                        'usuario:id,name',
                        'etapa:id,nome_etapa'
                    ]);
                },
            ])
            ->findOrFail($id);

        return response()->json($pedido);
    }
}
