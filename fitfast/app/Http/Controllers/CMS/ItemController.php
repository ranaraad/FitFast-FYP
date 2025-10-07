<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('users')->paginate(10);
        return view('cms.items.index', compact('items'));
    }

    public function create()
    {
        return view('cms.items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sizing_data' => 'nullable|json',
            'category' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        Item::create($validated);

        return redirect()->route('cms.items.index')
            ->with('success', 'Item created successfully.');
    }

    // ... other resource methods (show, edit, update, destroy)

    /**
     * Get AI recommendations for a user based on their measurements
     */
    public function getRecommendations(User $user)
    {
        // This will be implemented later with AI logic
        $userMeasurements = $user->measurements;

        // Placeholder - will query items based on user measurements
        $recommendations = Item::where('category', 'like', '%clothing%')
            ->limit(10)
            ->get();

        return response()->json($recommendations);
    }
}
