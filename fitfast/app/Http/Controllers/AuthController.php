<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; 
use App\Models\User;
use App\Models\Role;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // REGISTER
public function register(Request $request)
{
    try {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $role = Role::where('name', 'User')->first();

        if (!$role) {
            Log::error('Register failed: User role not found.');
            return response()->json(['error' => 'User role not found.'], 500);
        }

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role_id' => $role->id,
        ]);

        if (!$user) {
            Log::error('Register failed: User creation failed.');
            return response()->json(['error' => 'User creation failed.'], 500);
        }

        // Small delay ensures transaction fully commits before Sanctum tries to create token
        sleep(1);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
            'token' => $token,
        ], 201);
    } catch (\Throwable $e) {
        Log::error('Registration Exception: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        return response()->json([
            'error' => 'Registration failed.',
            'details' => $e->getMessage(),
        ], 500);
    }
}



    // LOGIN
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
