<?php

namespace App\Http\Controllers\CMS\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Show the CMS registration form.
     */
    public function showRegistrationForm()
    {
        $roles = Role::whereIn('name', ['Admin', 'Store Admin'])->get();
        return view('cms.pages.auth.register', compact('roles'));
    }

    /**
     * Handle a registration request for the CMS.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        // Verify that the selected role is either Admin or Store Admin
        $selectedRole = Role::find($validated['role_id']);
        if (!$selectedRole || !in_array($selectedRole->name, ['Admin', 'Store Admin'])) {
            throw ValidationException::withMessages([
                'role_id' => 'Invalid role selected for CMS registration.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to verification notice instead of dashboard
        return redirect()->route('verification.notice')->with('success', 'Registration successful! Please verify your email address.');
    }
}
