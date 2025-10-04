<?php

namespace App\Http\Controllers\CMS;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(10);
        return view('cms.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('cms.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        Role::create($validated);

        return redirect()->route('cms.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('users');
        return view('cms.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        return view('cms.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update($validated);

        return redirect()->route('cms.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // Prevent deletion if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('cms.roles.index')
                ->with('error', 'Cannot delete role that has users assigned.');
        }

        $role->delete();

        return redirect()->route('cms.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
