<?php

namespace App\Services\Conecta;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ConectaClient
{
    public function fetchPedidos(): array
    {
        $baseUrl = rtrim((string) config('services.conecta.base_url'), '/');
        $token   = (string) config('services.conecta.token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Conecta não configurado (CONECTA_API_URL / CONECTA_API_TOKEN).');
        }

        $url = "{$baseUrl}/api/pedidos_conecta.php?token=" . urlencode($token);

        $response = Http::timeout(20)
            ->retry(2, 300)
            ->withHeaders([
                'Accept'     => 'application/json',
                'User-Agent' => 'CPMH-Laravel/1.0',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException("Conecta HTTP {$response->status()}: " . $response->body());
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new RuntimeException('Conecta retornou JSON inválido.');
        }

        $pedidos = $json['pedidos'] ?? null;
        if (!is_array($pedidos)) {
            throw new RuntimeException('Conecta: chave "pedidos" não encontrada.');
        }

        return $pedidos;
    }
}
