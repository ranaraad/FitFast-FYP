<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckStoreAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is Store Admin
        if (!$user->role || $user->role->name !== 'Store Admin') {
            Auth::logout();
            return redirect()->route('cms.login');
        }

        return $next($request);
    }
}
