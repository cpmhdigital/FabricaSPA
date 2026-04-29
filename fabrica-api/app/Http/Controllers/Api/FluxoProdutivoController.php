<?php

namespace App\Http\Controllers;

use App\Models\Fluxo;
use Illuminate\Http\Request;

class FluxoProdutivoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_fluxo' => 'required|string|max:255',
            'etapas' => 'required|array|min:1',
            'etapas.*.ordem' => 'required|integer',
            'etapas.*.etapa' => 'required|string|max:255',
        ]);

        $fluxo = Fluxo::create([
            'nome_fluxo' => $validated['nome_fluxo'],
        ]);

        foreach ($validated['etapas'] as $etapaData) {
            $fluxo->etapas()->create([
                'ordem' => $etapaData['ordem'],
                'etapa' => $etapaData['etapa'],
                'tempo_estimado_minutos' => $etapaData['tempo_estimado_minutos'],
            ]);
        }

        return response()->json([
            'message' => 'Fluxo produtivo salvo com sucesso!',
            'data' => $fluxo->load('etapas')
        ], 201);
    }
}
