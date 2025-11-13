<?php

namespace App\Http\Controllers\CMS;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::with(['user', 'items'])
            ->withCount([
                'items',
                'items as low_stock_items_count' => function($query) {
                    $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
                },
                'items as critical_stock_items_count' => function($query) {
                    $query->where('stock_quantity', '<', 5)->where('stock_quantity', '>', 0);
                },
                'items as out_of_stock_items_count' => function($query) {
                    $query->where('stock_quantity', 0);
                }
            ])
            ->get();

        return view('cms.pages.stores.index', compact('stores'));
    }

    public function create()
    {
        $users = User::whereHas('role', function($query) {
            $query->where('name', 'Store Admin');
        })->get();

        return view('cms.pages.stores.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Store::create($validated);

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function show(Store $store)
    {
        $store->load(['user', 'items']);
        return view('cms.pages.stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $users = User::whereHas('role', function($query) {
            $query->where('name', 'Store Admin');
        })->get();

        return view('cms.pages.stores.edit', compact('store', 'users'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $store->update($validated);

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store deleted successfully.');
    }
}
