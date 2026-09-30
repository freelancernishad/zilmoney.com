<?php

namespace App\Http\Middleware;

use App\Models\AllowedOrigin;
use Closure;
use Throwable;

class Cors
{
    public function handle($request, Closure $next)
    {
        $origin = $request->header('Origin');
        $allowOrigin = null;

        if ($origin) {
            $isAllowedInDb = false;
            try {
                $isAllowedInDb = AllowedOrigin::where('origin_url', $origin)
                    ->orWhere('origin_url', '*')
                    ->exists();
            } catch (Throwable $e) {
                // Fallback if DB table is unavailable during migration/seeding
                $isAllowedInDb = false;
            }

            $isLocalDev = preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:[0-9]+)?$/', $origin);

            if ($isAllowedInDb || $isLocalDev || str_ends_with($origin, '.zsi.ai') || str_ends_with($origin, '.goldenmark.money')) {
                $allowOrigin = $origin;
            }
        }

        if (!$allowOrigin) {
            $allowOrigin = 'https://goldenmark.money';
        }

        @header_remove('X-Powered-By');
        @header_remove('Server');

        if ($request->isMethod('OPTIONS')) {
            $optionsResponse = response('', 200)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, Accept, Origin, Application')
                ->header('Access-Control-Allow-Credentials', 'true');

            if (isset($optionsResponse->headers)) {
                $optionsResponse->headers->remove('X-Powered-By');
                $optionsResponse->headers->remove('Server');
            }

            return $optionsResponse;
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, Accept, Origin, Application')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        if (isset($response->headers)) {
            $response->headers->remove('X-Powered-By');
            $response->headers->remove('Server');
        }

        return $response;
    }
}


