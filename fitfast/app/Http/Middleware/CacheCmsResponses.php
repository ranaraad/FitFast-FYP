<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheCmsResponses
{
    public function handle($request, Closure $next, $minutes = 60)
    {
        // Convert minutes to integer
        $minutes = (int) $minutes;

        // Only cache GET requests for CMS
        if ($request->isMethod('get') && $request->is('cms/*')) {
            $key = 'response.cms.' . md5($request->fullUrl());

            // Return cached response if exists
            if (Cache::has($key)) {
                return Cache::get($key);
            }

            // Get response and cache it
            $response = $next($request);

            // Only cache successful responses (200-299 status codes)
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                // Use integer minutes
                Cache::put($key, $response, now()->addMinutes($minutes));
            }

            return $response;
        }

        return $next($request);
    }
}
