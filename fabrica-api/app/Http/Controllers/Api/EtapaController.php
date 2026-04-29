<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Etapa\EtapaService;
use Illuminate\Http\Request;

class EtapaController extends Controller
{
    public function __construct(
        protected EtapaService $service
    ) {}

    public function index()
    {
        return $this->service->index();
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $etapa = $this->service->store($validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa criada com sucesso.',
            'etapa' => $etapa
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRequest($request);

        $etapa = $this->service->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa atualizada com sucesso.',
            'etapa' => $etapa
        ]);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);

        return response()->json(null, 204);
    }

    /**
     * Validação centralizada.
     * Ajustei nomes dos campos e regras inconsistentes.
     */
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'nome_etapa' => 'required|string|max:255',

            // setor
            'setor_op_id' => 'nullable|exists:setor_ops,id',

            // flags booleanas
            'colaboracao_multipla' => 'boolean',
            'obrigatorio_mp' => 'boolean',
            'anexo' => 'boolean',

            // máquinas
            'maquinas' => 'array',
            'maquinas.*' => 'integer|exists:maquina,id',

            // IT / REV vinculado
            'it_revs' => 'array',
            'it_revs.*' => 'integer|exists:it_revs,id',

            // parâmetros
            'parametros' => 'array',
            'parametros.*.id' => 'nullable|integer|exists:etapa_parametros,id',
            'parametros.*.nome' => 'required|string|max:255',
            'parametros.*.tipo' => 'required|in:texto,numero,simnao,data',
            'parametros.*.obrigatorio' => 'boolean',
            'parametros.*.limite' => 'nullable|string',
            'parametros.*.min' => 'nullable|numeric',
            'parametros.*.max' => 'nullable|numeric',

            // ordem sempre virá como null e o service define como 1
            'parametros.*.ordem' => 'nullable',

            // checklists
            'checklist_pre' => 'array',
            'checklist_pre.*' => 'string|max:255',

            'checklist_pos' => 'array',
            'checklist_pos.*' => 'string|max:255',
        ]);
    }
}
