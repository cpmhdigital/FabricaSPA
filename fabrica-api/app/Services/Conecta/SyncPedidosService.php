<?php

namespace App\Services\Conecta;

use App\Models\Pedido\Pedido;
use App\Models\Pedido\PedidoItem;
use App\Models\Pedido\PedidoItemUnidade;
use App\Models\Itens;
use Illuminate\Support\Facades\DB;

class SyncPedidosService
{
    public function sync(array $pedidosConecta): int
    {
        $syncCount = 0;

        DB::transaction(function () use ($pedidosConecta, &$syncCount) {

            foreach ($pedidosConecta as $p) {
                $pedidoData = PedidoMapper::toPedido($p);

                if (empty($pedidoData['numero_pedido'])) {
                    continue;
                }

                // 1) UPSERT do pedido
                $pedido = Pedido::updateOrCreate(
                    ['numero_pedido' => $pedidoData['numero_pedido']],
                    $pedidoData
                );

                // 2) Produtos do pedido
                $codigosProdutos = PedidoMapper::produtos($p);

                foreach ($codigosProdutos as $codigo) {

                    $item = Itens::firstOrCreate(
                        ['codigo' => $codigo],
                        [
                            'descricao' => $codigo,
                            'anvisa'    => 'PENDENTE',
                            'tipo'      => 'PENDENTE',
                            'fluxo_id'  => null,
                        ]
                    );


                    // 2.2) Quantidade (se não existir no Conecta, fica 1)
                    $qtd = (int)($p['quantidade'] ?? $p['qtd'] ?? 1);
                    $qtd = max(1, $qtd);

                    // 2.3) UPSERT do pedido_item
                    $pedidoItem = PedidoItem::updateOrCreate(
                        [
                            'pedido_id'  => $pedido->id,
                            'produto_id' => $item->id,
                        ],
                        [
                            'quantidade' => $qtd,
                        ]
                    );

                    // 2.4) Criar unidades conforme quantidade
                    $this->garantirUnidades($pedidoItem, $qtd);
                }

                $syncCount++;
            }
        });

        return $syncCount;
    }

    private function garantirUnidades(PedidoItem $pedidoItem, int $qtd): void
    {
        $existCount = PedidoItemUnidade::where('pedido_item_id', $pedidoItem->id)->count();

        if ($existCount >= $qtd) {
            // Não apaga unidades automaticamente
            return;
        }

        for ($i = $existCount + 1; $i <= $qtd; $i++) {
            PedidoItemUnidade::create([
                'pedido_item_id' => $pedidoItem->id,
                'unidade_codigo' => "{$pedidoItem->id}/{$i}",
            ]);
        }
    }
}
