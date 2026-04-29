<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMateriaPrimaRequest;
use App\Services\MateriaPrimaService;
use App\Models\Producao\MateriaPrima;

class MateriaPrimaController extends Controller
{
    private $service;

    public function __construct(MateriaPrimaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $pedidoId = $request->get('pedido_id');
        $unidadeId = $request->get('pedido_item_unidade_id');
        $etapaId = $request->get('etapa_id');

        return response()->json(
            $this->service->listarFiltrado($pedidoId, $unidadeId, $etapaId),
            200
        );
    }


    public function store(StoreMateriaPrimaRequest $request)
    {
        $this->service->salvarMateriasPrimas($request->validated());

        return response()->json([
            'message' => 'Matérias-primas registradas com sucesso.',
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            MateriaPrima::with(['item'])->findOrFail($id)
        );
    }

    public function update(StoreMateriaPrimaRequest $request, $id)
    {
        $mp = $this->service->atualizar($id, $request->validated());

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
            'data' => $mp
        ]);
    }

    public function destroy($id)
    {
        $this->service->remover($id);

        return response()->json([
            'message' => 'Registro removido com sucesso.'
        ]);
    }
}
