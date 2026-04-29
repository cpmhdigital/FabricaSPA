<?php

namespace App\Services;

use App\Models\Itens;
use App\Models\ItensComposicao;
use Illuminate\Support\Facades\Log;


class ProdutoService
{
    /**
     * Cria produto e seus itens (componentes/MPs) de forma inteligente
     */
    public function criarProdutoComItens(array $dados)
    {

        $produto = Itens::create([
            'codigo' => $dados['codigo'],
            'descricao' => $dados['descricao'],
            'tipo' => $dados['tipo'],
            'anvisa' => $dados['anvisa'] ?? null,
            'fluxo_id' => $dados['fluxo_id'] ?? null,
        ]);

        if (!empty($dados['itens'])) {
            foreach ($dados['itens'] as $item) {
                $this->criarComposicao($produto, $item);
            }
        }

        return $produto;
    }


    private function criarComposicao(Itens $produto, array $item)
    {
        try {
            // Cria (ou obtém) o item filho
            $filho = Itens::firstOrCreate(
                ['codigo' => $item['codigo']],
                [
                    'descricao' => $item['descricao'] ?? null,
                    'tipo' => $item['tipo'] ?? null,
                    'anvisa' => $item['anvisa'] ?? null,
                    'fluxo_id' => $item['fluxo_id'] ?? null,
                ]
            );

            // 🔹 Verifica se a composição já existe para este produto
            $exists = ItensComposicao::where('item_pai_id', $produto->id)
                ->where('item_filho_id', $filho->id)
                ->exists();

            if (!$exists) {
                // Cria o relacionamento de composição
                ItensComposicao::create([
                    'item_pai_id' => $produto->id,
                    'item_filho_id' => $filho->id,
                ]);
            }
            // Se já existir, ele simplesmente não cria, evitando duplicação
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
