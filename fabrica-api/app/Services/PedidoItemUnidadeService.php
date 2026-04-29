<?php

namespace App\Services;

use App\Models\Pedido\PedidoItem;
use App\Models\Pedido\PedidoItemUnidade;

class PedidoItemUnidadeService
{
    public function sincronizarUnidades(PedidoItem $item): void
    {
        PedidoItemUnidade::where('pedido_item_id', $item->id)->delete();

        $quantidade = (int) $item->quantidade;

        for ($i = 1; $i <= $quantidade; $i++) {
            PedidoItemUnidade::create([
                'pedido_item_id' => $item->id,
                'unidade_codigo' => "{$i}/{$quantidade}",
            ]);
        }
    }
}
