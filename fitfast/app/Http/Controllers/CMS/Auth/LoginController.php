<?php

namespace App\Http\Controllers\CMS\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the CMS login form.
     */
    public function showLoginForm()
    {
        return view('cms.pages.auth.login');
    }

    /**
     * Handle a login request to the CMS.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Check if user has CMS access role (Super Admin or Store Admin)
            $user = Auth::user();

            if (!$user->role || !in_array($user->role->name, ['Admin', 'Store Admin'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'You do not have access to the CMS.',
                ]);
            }

            // Redirect based on role
            if ($user->role->name === 'Admin') {
                return redirect()->intended(route('cms.dashboard'));
            } elseif ($user->role->name === 'Store Admin') {
                return redirect()->intended(route('store-admin.dashboard'));
            }
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out of the CMS.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cms.login');
    }
}
