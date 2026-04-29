<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\UserSession;

class InactivityLogout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            $now = Carbon::now();

            if ($lastActivity && $now->diffInMinutes($lastActivity) > 60) {
                $user = Auth::user();
                if ($user) {
                    UserSession::where('user_id', $user->id)
                        ->whereNull('logged_out_at')
                        ->update(['logged_out_at' => now()]);
                }

                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return response()->json(['message' => 'Sessão expirada por inatividade.'], 401);
            }


            session(['last_activity' => $now]);
        }

        return $next($request);
    }
}
