<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Add warning message for unauthenticated access attempts
        if ($request->is('cms/*') || $request->is('store-admin/*')) {
            return route('cms.login');
        }

        return route('cms.login');
    }
}
