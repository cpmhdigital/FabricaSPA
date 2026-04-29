<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SetorOp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetorOpController extends Controller
{
    // Listar todos os setores
    public function index()
    {
        $setores = SetorOp::all(); // Pega todos os setores
        return response()->json($setores, 200); // Retorna como JSON com status 200
    }

    // Criar um novo setor
    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400); // Retorna erro se a validação falhar
        }

        // Criando o setor
        $setor = SetorOp::create([
            'nome' => $request->nome,
        ]);

        return response()->json($setor, 201); // Retorna o setor criado com status 201
    }

    // Exibir um setor específico
    public function show($id)
    {
        $setor = SetorOp::find($id);

        if (!$setor) {
            return response()->json(['message' => 'Setor não encontrado'], 404); // Retorna erro 404 se o setor não for encontrado
        }

        return response()->json($setor, 200); // Retorna o setor encontrado com status 200
    }

    // Atualizar um setor existente
    public function update(Request $request, $id)
    {
        // Validação dos dados recebidos
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400); // Retorna erro se a validação falhar
        }

        $setor = SetorOp::find($id);

        if (!$setor) {
            return response()->json(['message' => 'Setor não encontrado'], 404); // Retorna erro 404 se o setor não for encontrado
        }

        // Atualiza o setor
        $setor->nome = $request->nome;
        $setor->save();

        return response()->json($setor, 200); // Retorna o setor atualizado com status 200
    }

    // Deletar um setor
    public function destroy($id)
    {
        $setor = SetorOp::find($id);

        if (!$setor) {
            return response()->json(['message' => 'Setor não encontrado'], 404);
        }

        $setor->delete(); // Agora será soft delete
        return response()->json(['message' => 'Setor deletado com sucesso'], 200);
    }


    public function associarEtapas(Request $request)
    {
        $validated = $request->validate([
            'setor_id'   => 'required|exists:setor_ops,id',
            'etapas'     => 'required|array',
            'etapas.*'   => 'exists:etapas,id',
        ]);

        foreach ($validated['etapas'] as $etapaId) {
            $etapa = \App\Models\Etapa::find($etapaId);
            $etapa->setor_op_id = $validated['setor_id'];
            $etapa->save();
        }

        return response()->json(['message' => 'Etapas associadas com sucesso!']);
    }
}
