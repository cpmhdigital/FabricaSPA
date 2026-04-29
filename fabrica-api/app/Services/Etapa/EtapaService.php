<?php

namespace App\Services\Etapa;

use App\Models\Etapa;
use App\Services\Etapa\Actions\SyncParametros;
use App\Services\Etapa\Actions\SyncChecklists;
use Illuminate\Support\Facades\DB;

class EtapaService
{
    public function __construct(
        protected SyncParametros $syncParametros,
        protected SyncChecklists $syncChecklists
    ) {}

    public function index()
    {
        return Etapa::with([
            'parametros',
            'maquinas',
            'itRevs',
            'checklistsPre',
            'checklistsPos'
        ])->get();
    }

    public function show($id)
    {
        return Etapa::with([
            'parametros',
            'maquinas',
            'itRevs',
            'checklistsPre',
            'checklistsPos'
        ])->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $etapa = Etapa::create($this->extractEtapaFields($data));

            $this->syncRelations($etapa, $data);

            return $this->show($etapa->id);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $etapa = Etapa::findOrFail($id);
            $etapa->update($this->extractEtapaFields($data));

            $this->syncRelations($etapa, $data);

            return $this->show($etapa->id);
        });
    }

    public function destroy($id)
    {
        $etapa = Etapa::findOrFail($id);
        $etapa->delete();
    }

    private function extractEtapaFields(array $data)
    {
        return collect($data)->except([
            'maquinas',
            'it_revs',
            'parametros',
            'checklist_pre',
            'checklist_pos'
        ])->toArray();
    }

    private function syncRelations(Etapa $etapa, array $data)
    {
        $etapa->maquinas()->sync($data['maquinas'] ?? []);
        $etapa->itRevs()->sync($data['it_revs'] ?? []);

        $this->syncParametros->execute($etapa, $data['parametros'] ?? []);
        $this->syncChecklists->execute($etapa, 'pre', $data['checklist_pre'] ?? []);
        $this->syncChecklists->execute($etapa, 'pos', $data['checklist_pos'] ?? []);
    }
}
