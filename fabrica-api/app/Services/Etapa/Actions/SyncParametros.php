<?php

namespace App\Services\Etapa\Actions;

use App\Models\Etapa;

class SyncParametros
{
    public function execute(Etapa $etapa, array $params)
    {
        $ids = collect($params)->pluck('id')->filter();

        $etapa->parametros()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($params as $param) {

            $dados = [
                'nome' => $param['nome'],
                'tipo' => $param['tipo'],
                'min' => $param['min'] ?? null,
                'max' => $param['max'] ?? null,
                'limite' => $param['limite'] ?? null,
                'obrigatorio' => $param['obrigatorio'] ?? false,
                'ordem' => $param['ordem'] ?? 1,
            ];

            if (!empty($param['id'])) {
                $etapa->parametros()->find($param['id'])->update($dados);
            } else {
                $etapa->parametros()->create($dados);
            }
        }
    }
}
