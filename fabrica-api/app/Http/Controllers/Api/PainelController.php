<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PainelController extends Controller
{
    public function cards(Request $request): JsonResponse
    {
        // Evita 500 por tabela/coluna inexistente.
        if (!Schema::hasTable('pedidos')) {
            return response()->json([
                'para_aprovacao' => 0,
                'em_execucao' => 0,
                'em_atraso' => 0,
                'novos' => 0,
                'debug' => 'Tabela pedidos não existe',
            ], 200);
        }

        if (!Schema::hasColumn('pedidos', 'status')) {
            return response()->json([
                'para_aprovacao' => 0,
                'em_execucao' => 0,
                'em_atraso' => 0,
                'novos' => 0,
                'debug' => 'Coluna pedidos.status não existe',
            ], 200);
        }

        // Status que você confirmou que existem
        $novos = DB::table('pedidos')->where('status', 'aguardando')->count();

        // Sem etapas/qualidade, o "em execução" só pode ser aproximado
        // Aqui: "aprovado" = entrou para produção
        $emExecucao = DB::table('pedidos')->where('status', 'aprovado')->count();

        // Ainda não existe sua lógica de qualidade/etapa => por enquanto 0
        $paraAprovacao = 0;

        // Ainda não existe sua lógica de atraso (etapa + data) => por enquanto 0
        $emAtraso = 0;

        return response()->json([
            'para_aprovacao' => $paraAprovacao,
            'em_execucao' => $emExecucao,
            'em_atraso' => $emAtraso,
            'novos' => $novos,
        ]);
    }
}
