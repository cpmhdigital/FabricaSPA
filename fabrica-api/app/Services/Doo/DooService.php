<?php

namespace App\Services\Doo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DooService
{
    private string $baseUrl;
    private string $tokenUrl;
    private string $clientId;
    private string $clientSecret;
    private string $scope;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.doo.base_url'), '/');
        $this->tokenUrl = (string) config('services.doo.token_url');
        $this->clientId = (string) config('services.doo.client_id');
        $this->clientSecret = (string) config('services.doo.client_secret');
        $this->scope = (string) config('services.doo.scope');
    }

    /**
     * Client HTTP autenticado com Bearer token
     */
    private function client()
    {
        $token = $this->getAccessToken();

        return Http::withToken($token)
            ->acceptJson()
            ->timeout(20);
    }

    /**
     * Busca token via OAuth Client Credentials e guarda em cache.
     * Ajuste o payload conforme a documentação do DOO.
     */
    private function getAccessToken(): string
    {
        return Cache::remember('doo_access_token', now()->addMinutes(50), function () {
            $payload = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ];

            if (!empty($this->scope)) {
                $payload['scope'] = $this->scope;
            }

            // Alguns provedores exigem form-url-encoded:
            $res = Http::asForm()
                ->acceptJson()
                ->timeout(20)
                ->post($this->tokenUrl, $payload);

            if (!$res->successful()) {
                Log::error('DOO token error', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);
                throw new \Exception('Falha ao obter token do DOO.');
            }

            $json = $res->json();

            // Padrão OAuth: { access_token, token_type, expires_in }
            $token = $json['access_token'] ?? null;

            if (!$token) {
                Log::error('DOO token missing access_token', ['json' => $json]);
                throw new \Exception('Token do DOO inválido (access_token ausente).');
            }

            /**
             * Se o DOO retornar expires_in (segundos), você pode usar isso:
             * $ttl = max(60, ((int)($json['expires_in'] ?? 3600)) - 120);
             * Cache::put('doo_access_token', $token, $ttl);
             * return $token;
             *
             * Aqui mantive remember() com 50 min, que costuma funcionar bem.
             */
            return $token;
        });
    }

    /**
     * Útil para tela /doo/integracao (health check).
     */
    public function health(): array
    {
        // tenta pegar token e opcionalmente pingar endpoint leve
        $token = $this->getAccessToken();

        return [
            'message' => $token ? 'Conexão OK (token obtido).' : 'Sem token',
        ];
    }

    /**
     * Exemplo: listar matrizes no DOO
     * Ajuste o endpoint conforme o DOO.
     */
    public function matrizes(): array
    {
        $res = $this->client()->get("{$this->baseUrl}/matrizes");

        // Se token expirar e o DOO retornar 401, força refresh 1 vez
        if ($res->status() === 401) {
            Cache::forget('doo_access_token');
            $res = $this->client()->get("{$this->baseUrl}/matrizes");
        }

        if (!$res->successful()) {
            Log::error('DOO matrizes error', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            throw new \Exception('Erro ao consultar matrizes no DOO.');
        }

        return $res->json() ?? [];
    }
}
