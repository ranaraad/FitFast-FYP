<?php

namespace App\Http\Controllers\CMS;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('stores/logo', 'public');
            $validated['logo'] = $logoPath;
        } else {
            $validated['logo'] = null;
        }

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('stores/banner', 'public');
            $validated['banner_image'] = $bannerPath;
        } else {
            $validated['banner_image'] = null;
        }

        // Clean contact_info if it contains JSON quotes
        if (isset($validated['contact_info'])) {
            $validated['contact_info'] = trim($validated['contact_info'], '"');
        }

        try {
            Store::create($validated);
        } catch (\Exception $e) {
            // Delete uploaded files if store creation fails
            if (isset($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            if (isset($bannerPath)) {
                Storage::disk('public')->delete($bannerPath);
            }

            return back()->withInput()->with('error', 'Store creation failed: ' . $e->getMessage());
        }

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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Clean contact_info if it contains JSON quotes
        if (isset($validated['contact_info'])) {
            $validated['contact_info'] = trim($validated['contact_info'], '"');
        }

        // Handle logo upload or removal
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores/logo', 'public');
        } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
            // Remove logo if requested
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = null;
        } else {
            // Keep existing logo if no new file and no removal request
            $validated['logo'] = $store->logo;
        }

        // Handle banner image upload or removal
        if ($request->hasFile('banner_image')) {
            // Delete old banner if exists
            if ($store->banner_image && Storage::disk('public')->exists($store->banner_image)) {
                Storage::disk('public')->delete($store->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('stores/banner', 'public');
        } elseif ($request->has('remove_banner') && $request->remove_banner == '1') {
            // Remove banner if requested
            if ($store->banner_image && Storage::disk('public')->exists($store->banner_image)) {
                Storage::disk('public')->delete($store->banner_image);
            }
            $validated['banner_image'] = null;
        } else {
            // Keep existing banner if no new file and no removal request
            $validated['banner_image'] = $store->banner_image;
        }

        try {
            $store->update($validated);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Store update failed: ' . $e->getMessage());
        }

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        // Delete associated files
        if ($store->logo && Storage::disk('public')->exists($store->logo)) {
            Storage::disk('public')->delete($store->logo);
        }
        if ($store->banner_image && Storage::disk('public')->exists($store->banner_image)) {
            Storage::disk('public')->delete($store->banner_image);
        }

        $store->delete();

        return redirect()->route('cms.stores.index')
            ->with('success', 'Store deleted successfully.');
    }
}
