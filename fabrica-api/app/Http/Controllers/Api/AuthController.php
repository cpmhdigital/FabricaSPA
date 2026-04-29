<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Events\UsuariosOnlineAtualizado;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = Str::lower($request->input('email'));

        if (!auth()->attempt(['email' => $email, 'password' => $request->password])) {
            return response()->json(['message' => 'Credenciais incorretas'], 401);
        }

        $user = User::where('email', $email)->firstOrFail();

        if (is_null($user->email_verified_at)) {
            $user->sendEmailVerificationNotification();
            return response()->json(['message' => 'Verifique seu E-mail.'], 403);
        }

        if (in_array($user->status, ['aguardando', 'inativo'])) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $session = UserSession::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
        ]);

        $token = $user->createToken('api-token', ['user_session_id:' . $session->id])->plainTextToken;

        $permissions = $user->getAllPermissions()->pluck('name');

        $dashUrl = match (true) {
            $permissions->contains('menu painel producao') => '/ordem-producao/painel-producao',
            $permissions->contains('menu painel manutencao') => '/ordem-manutencao/painel-manutencao',
            $permissions->contains('menu painel os') => '/ordem-servico/painel-servico',
            $permissions->contains('menu doo matrizes') => '/doo/matrizes',
            $permissions->contains('menu dashboard') => '/painel-atividade',
            default => '/painel-atividade',
        };

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nome' => $user->name,
                'email' => $user->email,
                 'email_verified_at' => $user->email_verified_at,
                'departamento_id' => $user->departamento_id,
                'departamento_nome' => optional($user->departamento)->nome,
            ],
            'redirect_to' => $dashUrl,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        $sessionId = collect($token->abilities)
            ->first(fn($ability) => str_starts_with($ability, 'user_session_id:'));

        if ($sessionId) {
            $id = (int) str_replace('user_session_id:', '', $sessionId);

            UserSession::where('id', $id)
                ->where('user_id', $user->id)
                ->update([
                    'logged_out_at' => now(),
                ]);
        }

        $token->delete();

        return response()->json(['message' => 'Logout feito com sucesso']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'E-mail não encontrado.'], 404);
        }

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);

        return response()->json(['message' => 'Link de recuperação enviado para o seu e-mail.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Senha resetada com sucesso.']);
        }

        throw ValidationException::withMessages(['email' => [trans($status)]]);
    }
}
