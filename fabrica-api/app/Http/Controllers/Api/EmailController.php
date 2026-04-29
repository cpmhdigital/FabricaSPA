<?php
/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EmailController extends Controller
{
    public function enviar(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado'], 401);
            $user->sendEmailVerificationNotification();
        }

        if (!is_null($user->email_verified_at)) {
            return response()->json(['message' => 'E-mail já verificado.']);
        }

        return response()->json(['message' => 'E-mail de verificação reenviado!']);
    }
}
 */
