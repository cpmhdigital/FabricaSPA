<?php

namespace App\Services\Bom;

use App\Models\ItensComposicao;
use App\Models\Pedido\Pedido;

class ExpandPedidoItensService
{
    public function expand(Pedido $pedido): array
    {
        // Carrega itens do pedido + produto
        $pedido->load(['pedidoItens.produto']);

        $itens = [];

        foreach ($pedido->pedidoItens as $pi) {
            $produto = $pi->produto;
            if (!$produto) continue;

            // Busca BOM mestre desse produto
            $bom = ItensComposicao::with('componente')
                ->where('produto_id', $produto->id)
                ->orderBy('id')
                ->get();

            $itens[] = [
                'id' => $pi->id,
                'produto' => [
                    'id' => $produto->id,
                    'codigo' => $produto->codigo,
                    'descricao' => $produto->descricao,
                    'tipo' => $produto->tipo ?? null,
                ],
                'quantidade' => $pi->quantidade ?? 1,

                // ISSO é o que seu Vue espera: pi.componentes[]
                'componentes' => $bom->map(function ($row) {
                    return [
                        'componente_id' => $row->componente_id,
                        'quantidade'    => $row->quantidade ?? 1,
                        'tipo'          => $row->tipo ?? 'normal',
                        'componente'    => $row->componente ? [
                            'id'        => $row->componente->id,
                            'codigo'    => $row->componente->codigo,
                            'descricao' => $row->componente->descricao,
                            'tipo'      => $row->componente->tipo ?? null,
                        ] : null,
                    ];
                })->values(),
            ];
        }

        return $itens;
    }
}
