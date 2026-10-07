<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-Factor Authentication (2FA) is required to connect a bank account. Please enable 2FA in your account security settings first.',
                'requires_2fa' => true,
            ], 403);
        }

        return $next($request);
    }
}
