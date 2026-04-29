<?php

namespace App\Services\Etapa\Actions;

use App\Models\Etapa;

class SyncChecklists
{
    public function execute(Etapa $etapa, string $tipo, array $nomes)
    {
        $relation = $tipo === 'pre' ? 'checklistsPre' : 'checklistsPos';

        $etapa->$relation()->whereNotIn('nome', $nomes)->delete();

        foreach ($nomes as $nome) {

            $check = $etapa->$relation()->withTrashed()->where('nome', $nome)->first();

            if ($check && $check->trashed()) {
                $check->restore();
                continue;
            }

            if (!$check) {
                $etapa->$relation()->create([
                    'nome' => $nome,
                    'tipo' => $tipo
                ]);
            }
        }
    }
}
