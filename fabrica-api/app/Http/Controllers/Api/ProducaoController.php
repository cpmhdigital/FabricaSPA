<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProducaoService;

class ProducaoController extends Controller
{
    protected $service;

    public function __construct(ProducaoService $service)
    {
        $this->service = $service;
    }

    /**
     * Retorna o progresso completo do pedido
     */
    public function show($id)
{
    return $this->service->gerarProgressoPedido($id); 
}

}
