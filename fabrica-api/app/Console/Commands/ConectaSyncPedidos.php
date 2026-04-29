<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Conecta\ConectaClient;
use App\Services\Conecta\SyncPedidosService;

class ConectaSyncPedidos extends Command
{
    protected $signature = 'conecta:sync-pedidos';
    protected $description = 'Sincroniza pedidos do Conecta para pedidos/pedido_item/pedido_item_unidade';

    public function handle(ConectaClient $client, SyncPedidosService $sync): int
    {
        $pedidos = $client->fetchPedidos();
        $total = $sync->sync($pedidos);

        $this->info("Pedidos sincronizados: {$total}");

        return Command::SUCCESS;
    }
}
