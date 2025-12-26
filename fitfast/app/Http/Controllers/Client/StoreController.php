<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Return a clean list of stores for the app homepage.
     */
    public function index(Request $request)
    {
        // Fetch all stores (only active ones ideally)
        $stores = Store::where('status', 'active')
            ->get()
            ->map(function ($store) {
                $logoUrl = null;
                if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                    $logoUrl = asset('storage/' . $store->logo);
                }

                $bannerUrl = null;
                if ($store->banner_image && Storage::disk('public')->exists($store->banner_image)) {
                    $bannerUrl = asset('storage/' . $store->banner_image);
                }


                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'description' => $store->description,
                    'logo_url' => $logoUrl,
                    'banner_url' => $bannerUrl,
                    'contact_info' => $store->contact_info,
                    'address' => $store->address,
                ];
            });

        return response()->json([
            'data' => $stores,
        ]);
    }

    /**
     * Show a single store with its categories and items.
     */
    public function show(Store $store)
    {
        if ($store->status !== 'active') {
            return response()->json([
                'message' => 'Store is not available.',
            ], 404);
        }

        $logoUrl = null;
        if ($store->logo && Storage::disk('public')->exists($store->logo)) {
            $logoUrl = asset('storage/' . $store->logo);
        }

        $bannerUrl = null;
        if ($store->banner_image && Storage::disk('public')->exists($store->banner_image)) {
            $bannerUrl = asset('storage/' . $store->banner_image);
        }

        $categories = Category::active()
            ->ordered()
            ->with([
                'items' => function ($query) use ($store) {
                    $query->where('store_id', $store->id)
                        ->orderBy('name');
                },
            ])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'items' => $category->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => $item->price,
                            'stock_quantity' => $item->stock_quantity,
                        ];
                    })->values(),
                ];
            })
            ->filter(function ($category) {
                return $category['items']->isNotEmpty();
            })
            ->values();

        return response()->json([
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'description' => $store->description,
                'logo_url' => $logoUrl,
                'banner_url' => $bannerUrl,
                'contact_info' => $store->contact_info,
                'address' => $store->address,
                'categories' => $categories,
            ],
        ]);
    }
}