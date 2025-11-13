<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class CheckCMSAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is Super Admin
        if (!$user->role || $user->role->name !== 'Admin') {
            Auth::logout();
            return redirect()->route('cms.login')->withErrors([
                'email' => 'You do not have access to the Super Admin CMS.'
            ]);
        }

        return $next($request);
    }
}
