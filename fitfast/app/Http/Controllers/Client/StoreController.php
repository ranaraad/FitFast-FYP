<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

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
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'description' => $store->description,
                    'logo_url' => $store->logo ? asset('storage/' . $store->logo) : null,
                    'banner_url' => $store->banner_image ? asset('storage/' . $store->banner_image) : null,
                    'contact_info' => $store->contact_info,
                    'address' => $store->address,
                ];
            });

        return response()->json([
            'data' => $stores,
        ]);
    }
}
