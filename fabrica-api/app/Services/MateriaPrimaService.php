<?php

namespace App\Services;

use App\Models\Producao\MateriaPrima;
use Illuminate\Support\Facades\DB;

class MateriaPrimaService
{
    public function salvarMateriasPrimas(array $dados)
    {
        return DB::transaction(function () use ($dados) {

            foreach ($dados['mps'] as $mp) {

                MateriaPrima::create([
                    'pedido_id' => $dados['pedido_id'],
                    'pedido_item_unidade_id' => $dados['pedido_item_unidade_id'],
                    'etapa_id' => $dados['etapa_id'],
                    'materia_prima_id' => $mp['mp_id'],
                    'valor' => $mp['valor'],
                    'unidade' => $mp['unidade'],
                    'lote' => $mp['lote'],
                    'usuario_id' => $dados['usuario_id'],
                ]);
            }

            return true;
        });
    }

    public function listarFiltrado(?int $pedidoId, ?int $unidadeId, ?int $etapaId)
    {
        return MateriaPrima::query()
            ->when($pedidoId, fn($q) => $q->where('pedido_id', $pedidoId))
            ->when($unidadeId, fn($q) => $q->where('pedido_item_unidade_id', $unidadeId))
            ->when($etapaId, fn($q) => $q->where('etapa_id', $etapaId))
            ->get();
    }

    public function atualizar(int $id, array $dados)
    {
        $mp = MateriaPrima::findOrFail($id);
        $mp->update($dados);
        return $mp;
    }

    public function remover(int $id)
    {
        $mp = MateriaPrima::findOrFail($id);
        $mp->delete();
        return true;
    }
}
