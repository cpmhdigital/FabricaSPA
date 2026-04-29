<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Itens;
use App\Models\Fluxo;
use Illuminate\Support\Facades\Auth;

class FluxoController extends Controller
{
    public function index()
    {
        return Fluxo::with('etapas')->get();
    }

    public function show($id)
    {
        $fluxo = Fluxo::with('etapas')->findOrFail($id);
        return response()->json($fluxo);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_fluxo' => 'required|string|max:255',
            'tempo_estimado_dias' => 'nullable|integer',
            'tempo_estimado_dias_acelerado' => 'nullable|integer',
            'etapas' => 'required|array|min:1',
            'etapas.*.id' => 'required|exists:etapa,id',
            'etapas.*.ordem' => 'required|integer|min:1',
            'etapas.*.tempo_estimado_minutos' => 'nullable|integer',
        ]);

        $fluxo = Fluxo::create([
            'nome_fluxo' => $validated['nome_fluxo'],
            'tempo_estimado_dias' => $validated['tempo_estimado_dias'] ?? null,
            'tempo_estimado_dias_acelerado' => $validated['tempo_estimado_dias_acelerado'] ?? null,
        ]);

        $syncData = [];
        foreach ($validated['etapas'] as $etapa) {
            $syncData[$etapa['id']] = [
                'ordem' => $etapa['ordem'],
                'tempo_estimado_minutos' => $etapa['tempo_estimado_minutos'] ?? null,
            ];
        }

        $fluxo->etapas()->sync($syncData);

        return response()->json([
            'message' => 'Fluxo criado com sucesso!',
            'data' => $fluxo->load('etapas'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $fluxo = Fluxo::findOrFail($id);

        $validated = $request->validate([
            'nome_fluxo' => 'sometimes|string|max:255',
            'tempo_estimado_dias' => 'sometimes|integer|min:1',
            'tempo_estimado_dias_acelerado' => 'sometimes|integer|min:1',
            'etapas' => 'sometimes|array',
            'etapas.*.id' => 'required|exists:etapa,id',
            'etapas.*.ordem' => 'required|integer|min:1',
            'etapas.*.tempo_estimado_minutos' => 'nullable|integer',
        ]);

        $fluxo->update($request->only([
            'nome_fluxo',
            'tempo_estimado_dias',
            'tempo_estimado_dias_acelerado',
        ]));

        if (isset($validated['etapas'])) {
            $syncData = [];
            foreach ($validated['etapas'] as $etapa) {
                $syncData[$etapa['id']] = [
                    'ordem' => $etapa['ordem'],
                    'tempo_estimado_minutos' => $etapa['tempo_estimado_minutos'] ?? null,
                ];
            }
            $fluxo->etapas()->sync($syncData);
        }

        return response()->json([
            'message' => 'Fluxo atualizado com sucesso!',
            'data' => $fluxo->load('etapas'),
        ]);
    }

    public function destroy($id)
    {
        $fluxo = Fluxo::findOrFail($id);
        $fluxo->delete();

        return response()->json(['message' => 'Fluxo removido com sucesso.']);
    }

    public function showFluxoItem($id)
    {
        $item = Itens::with(['fluxo.etapas'])->findOrFail($id);

        // 🧮 Soma correta dos minutos das etapas (usando pivot)
        $totalMinutos = $item->fluxo->etapas->sum(function ($etapa) {
            return $etapa->pivot->tempo_estimado_minutos;
        });

        // 🔢 Conversões
        $totalHoras = $totalMinutos / 60;         // total em horas
        $diasUteis = $totalHoras / 9;             // considerando 9h/dia útil
        $diasCorridos = ceil(($diasUteis / 5) * 7); // 5 dias úteis = 7 corridos
        $descricao = "Aproximadamente {$diasCorridos} dias corridos (considerando " .
            round($diasUteis, 2) . " dias úteis de 9h por dia).";

        return response()->json([
            'item' => $item,
            'fluxo' => $item->fluxo,
            'totalMinutos' => $totalMinutos,
            'totalHoras' => round($totalHoras, 2),
            'diasUteis' => round($diasUteis, 2),
            'diasCorridos' => $diasCorridos,
            'descricao' => $descricao,
        ]);
    }
}
