<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if (!URL::hasValidSignature($request)) {
            return response()->json(['message' => 'Assinatura inválida ou expirada.'], 403);
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Hash inválido.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            if ($user->hasVerifiedEmail()) {
                return redirect(rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/email-confirmado?status=already');
            }
        }


        $user->markEmailAsVerified();

        event(new Verified($user));

        return redirect(rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/email-confirmado');
    }
}
