<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->paginate(10);
        return view('cms.pages.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('cms.pages.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'height_cm' => $this->getMeasurementValidation($request->role_id, 'height_cm'),
            'weight_kg' => $this->getMeasurementValidation($request->role_id, 'weight_kg'),
            'shoe_size' => $this->getMeasurementValidation($request->role_id, 'shoe_size'),
            'address' => $this->getAddressValidation($request->role_id, 'address'),
            'shipping_address' => $this->getAddressValidation($request->role_id, 'shipping_address'),
            'billing_address' => $this->getAddressValidation($request->role_id, 'billing_address'),
        ]);

        // Build measurements array if user role
        if ($this->isUserRole($request->role_id)) {
            $validated['measurements'] = [
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'shoe_size' => $validated['shoe_size'],
            ];
        }

        // Remove individual measurement fields
        unset($validated['height_cm'], $validated['weight_kg'], $validated['shoe_size']);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('cms.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role');
        return view('cms.pages.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('cms.pages.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'height_cm' => $this->getMeasurementValidation($request->role_id, 'height_cm'),
            'weight_kg' => $this->getMeasurementValidation($request->role_id, 'weight_kg'),
            'shoe_size' => $this->getMeasurementValidation($request->role_id, 'shoe_size'),
            'address' => $this->getAddressValidation($request->role_id, 'address'),
            'shipping_address' => $this->getAddressValidation($request->role_id, 'shipping_address'),
            'billing_address' => $this->getAddressValidation($request->role_id, 'billing_address'),
        ]);

        // Build measurements array if user role
        if ($this->isUserRole($request->role_id)) {
            $validated['measurements'] = [
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'shoe_size' => $validated['shoe_size'],
            ];
        } else {
            // Clear measurements for non-user roles
            $validated['measurements'] = null;
        }

        // Remove individual measurement fields
        unset($validated['height_cm'], $validated['weight_kg'], $validated['shoe_size']);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('cms.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('cms.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get measurement validation rules based on role
     */
    private function getMeasurementValidation($roleId, $field)
    {
        if (!$this->isUserRole($roleId)) {
            return 'nullable|numeric';
        }

        $rules = [
            'height_cm' => 'required|numeric|min:100|max:250',
            'weight_kg' => 'required|numeric|min:30|max:200',
            'shoe_size' => 'required|numeric|min:30|max:50',
        ];

        return $rules[$field] ?? 'nullable|numeric';
    }

    /**
     * Get address validation rules based on role
     */
    private function getAddressValidation($roleId, $field)
    {
        if (!$this->isUserRole($roleId)) {
            return 'nullable|string|max:500';
        }

        return 'required|string|max:500';
    }

    /**
     * Check if role is a regular user (not admin)
     */
    private function isUserRole($roleId)
    {
        $role = Role::find($roleId);
        return $role && strtolower($role->name) === 'user';
    }
}
