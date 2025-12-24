<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
}
