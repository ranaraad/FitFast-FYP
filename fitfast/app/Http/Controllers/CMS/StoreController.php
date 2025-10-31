<?php

namespace App\Http\Controllers\CMS;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::withCount([
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
        ])->paginate(10);

        return view('cms.pages.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('cms.pages.stores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Store::create($validated);

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function show(Store $store)
    {
        $store->load('items');
        return view('cms.pages.stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        return view('cms.pages.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_info' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
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
