<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSession;  // Verifique se você tem esse modelo
use Carbon\Carbon;

class SessionController extends Controller
{
    public function __construct()
    {
        // Proteja o acesso se necessário
        $this->middleware('auth:sanctum');
    }

    public function usuariosOnline()
    {
        // Recupera todos os usuários com sessões ativas (logged_out_at é NULL)
        $usuariosOnline = UserSession::with('user') // Certifique-se de que existe o relacionamento `user` no modelo UserSession
            ->whereNull('logged_out_at') // Filtra sessões que ainda estão ativas
            ->orderByDesc('logged_in_at') // Ordena pela hora de login mais recente
            ->get()
            ->map(function ($session) {
                return [
                    'nome' => $session->user->name, // Nome do usuário
                    'email' => $session->user->email, // Email do usuário                                                                                                                                                                         
                    'ip' => $session->ip, // IP do usuário
                    'login_em' => Carbon::parse($session->logged_in_at)->format('d/m/Y H:i:s'), // Formata a data de login
                ];
            });

        return response()->json([
            'usuarios_online' => $usuariosOnline // Retorna o array com os usuários online
        ]);
    }
}
