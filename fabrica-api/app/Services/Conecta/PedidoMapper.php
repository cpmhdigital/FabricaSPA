<?php

namespace App\Services\Conecta;

class PedidoMapper
{
    public static function toPedido(array $p): array
    {
        return [
            'numero_pedido' => $p['pedNumPedido'] ?? null,
            'pedPropRef'    => $p['pedPropRef'] ?? null,
            'pedTipoProduto' => $p['pedTipoProduto'] ?? null,
            'lote'          => $p['loteop'] ?? null,
            'doutor'        => $p['pedNomeDr'] ?? null,
            'paciente'      => $p['pedNomePac'] ?? null,
            'data_pedido'   => $p['pedDtCriacaoPed'] ?? null,
            'data_aceite'  => $p['pedDtAceite'] ?? null,
        ];
    }

    public static function produtos(array $p): array
    {
        $raw = (string)($p['pedProduto'] ?? '');
        $parts = array_map('trim', explode('/', $raw));
        $parts = array_values(array_filter($parts, fn($x) => $x !== ''));
        return array_unique($parts);
    }
}
