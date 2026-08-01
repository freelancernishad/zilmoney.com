<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AttachJwtFromCookie
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
        $token = $request->bearerToken() ?? $request->query('token');

        if ($token === 'undefined' || $token === 'null') {
            $token = null;
            $request->headers->remove('Authorization');
        }

        if (!$token) {
            if ($request->is('user') || $request->is('user/*') || $request->is('api/user') || $request->is('api/user/*') || $request->is('api/auth/user') || $request->is('api/auth/user/*')) {
                 $token = $request->cookie('user_token') ?? $_COOKIE['user_token'] ?? null;
            } elseif ($request->is('admin') || $request->is('admin/*') || $request->is('api/admin') || $request->is('api/admin/*') || $request->is('api/auth/admin') || $request->is('api/auth/admin/*')) {
                 $token = $request->cookie('admin_token') ?? $_COOKIE['admin_token'] ?? null;
            }

            // Fallback or generic logic if needed
            if (!$token) {
                 if ($request->is('api/admin/*') || $request->is('admin/*') || $request->is('api/auth/admin/*')) {
                     $token = $request->cookie('admin_token') ?? $_COOKIE['admin_token'] ?? null;
                 } else {
                     $token = $request->cookie('user_token') ?? $_COOKIE['user_token'] ?? null;
                 }
            }
        }

        \Illuminate\Support\Facades\Log::info('AttachJwtFromCookie execution details:', [
            'path' => $request->path(),
            'method' => $request->method(),
            'admin_token_cookie' => $request->cookie('admin_token') ? substr($request->cookie('admin_token'), 0, 15) . '...' : null,
            'admin_token_superglobal' => isset($_COOKIE['admin_token']) ? substr($_COOKIE['admin_token'], 0, 15) . '...' : null,
            'resolved_token' => $token ? substr($token, 0, 15) . '...' : null,
            'existing_bearer' => $request->bearerToken() ? substr($request->bearerToken(), 0, 15) . '...' : null,
        ]);

        if ($token) {
            if (!$request->bearerToken()) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
            try {
                if ($request->is('api/admin/*') || $request->is('admin/*') || $request->is('api/auth/admin/*')) {
                    \Illuminate\Support\Facades\Auth::guard('admin')->setToken($token);
                    \Illuminate\Support\Facades\Log::info('Set token on admin guard. Check status: ' . (\Illuminate\Support\Facades\Auth::guard('admin')->check() ? 'Authenticated' : 'Failed'));
                } else {
                    \Illuminate\Support\Facades\Auth::guard('web')->setToken($token);
                    \Illuminate\Support\Facades\Auth::guard('api')->setToken($token);
                    \Illuminate\Support\Facades\Auth::guard('user')->setToken($token);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error setting token on guard: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
