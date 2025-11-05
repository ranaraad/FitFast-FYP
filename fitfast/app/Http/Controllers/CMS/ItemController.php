<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['store', 'category', 'users'])->get();
        return view('cms.pages.items.index', compact('items'));
    }

    public function create()
    {
        $stores = Store::all();
        $categories = Category::active()->ordered()->get();

        // Get all garment types data
        $garmentTypes = Item::GARMENT_TYPES;
        $standardSizes = Item::STANDARD_SIZES;
        $garmentTypesByCategory = Item::getGarmentTypesByCategory();

        // Get category to garment type mapping for JavaScript
        $categoryToGarmentTypes = $this->getCategoryToGarmentTypesMapping();

        return view('cms.pages.items.create', compact(
            'stores',
            'categories',
            'garmentTypes',
            'standardSizes',
            'garmentTypesByCategory',
            'categoryToGarmentTypes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'garment_type' => 'required|string|in:' . implode(',', array_keys(Item::GARMENT_TYPES)),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color_variants' => 'required|array',
            'color_variants.*.name' => 'required|string|max:255',
            'color_variants.*.stock' => 'required|integer|min:0',
            'size_stock' => 'required|array',
            'size_stock.*' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|array',
        ]);

        // Validate stock consistency
        $totalColorStock = 0;
        foreach ($request->color_variants as $colorData) {
            $totalColorStock += $colorData['stock'];
        }

        $totalSizeStock = array_sum($request->size_stock);

        if ($totalColorStock !== $totalSizeStock) {
            return redirect()->back()
                ->with('error', "Total color stock ($totalColorStock) must match total size stock ($totalSizeStock). Please adjust your stock levels.")
                ->withInput();
        }

        // Build color variants structure
        $colorVariants = [];
        foreach ($request->color_variants as $colorData) {
            $colorName = $colorData['name'];
            $colorVariants[$colorName] = [
                'name' => $colorName,
                'stock' => $colorData['stock']
            ];
        }
        $validated['color_variants'] = $colorVariants;

        // Build the proper sizing_data structure
        $validated['sizing_data'] = $this->buildSizingData($request);

        // Build size_stock data
        $validated['size_stock'] = $request->size_stock;

        // Calculate total stock (should be the same from both sources)
        $validated['stock_quantity'] = $totalColorStock; // or $totalSizeStock, they should be equal

        Item::create($validated);

        return redirect()->route('cms.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['store', 'category', 'users']);
        return view('cms.pages.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $stores = Store::all();
        $categories = Category::active()->ordered()->get();

        // Get all garment types data
        $garmentTypes = Item::GARMENT_TYPES;
        $standardSizes = Item::STANDARD_SIZES;
        $garmentTypesByCategory = Item::getGarmentTypesByCategory();

        // Get category to garment type mapping for JavaScript
        $categoryToGarmentTypes = $this->getCategoryToGarmentTypesMapping();

        return view('cms.pages.items.edit', compact(
            'item',
            'stores',
            'categories',
            'garmentTypes',
            'standardSizes',
            'garmentTypesByCategory',
            'categoryToGarmentTypes'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'garment_type' => 'required|string|in:' . implode(',', array_keys(Item::GARMENT_TYPES)),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color_variants' => 'required|array',
            'color_variants.*.name' => 'required|string|max:255',
            'color_variants.*.stock' => 'required|integer|min:0',
            'size_stock' => 'required|array',
            'size_stock.*' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|array',
        ]);

        // Build color variants structure
        $colorVariants = [];
        foreach ($request->color_variants as $colorData) {
            $colorName = $colorData['name'];
            $colorVariants[$colorName] = [
                'name' => $colorName,
                'stock' => $colorData['stock']
            ];
        }
        $validated['color_variants'] = $colorVariants;

        // Build the proper sizing_data structure (optional now)
        $validated['sizing_data'] = $this->buildSizingData($request);

        // Build size_stock data
        $validated['size_stock'] = $request->size_stock;

        // Calculate total stock from color variants
        $totalStock = 0;
        foreach ($colorVariants as $colorData) {
            $totalStock += $colorData['stock'];
        }
        $validated['stock_quantity'] = $totalStock;

        $item->update($validated);

        return redirect()->route('cms.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('cms.items.index')
            ->with('success', 'Item deleted successfully.');
    }

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

    /**
     * Build standardized sizing data structure
     */
    private function buildSizingData(Request $request)
    {
        $garmentType = $request->garment_type;

        // If no sizes provided, return empty structure
        if (empty($request->sizes)) {
            return [
                'garment_type' => $garmentType,
                'measurements_cm' => [],
                'fit_characteristics' => [
                    'fit_type' => $request->fit_type ?? 'regular',
                    'ease' => $request->ease ?? 'standard',
                    'stretch' => $request->stretch ?? 'none',
                ],
                'size_system' => $request->size_system ?? 'US',
                'last_updated' => now()->toISOString()
            ];
        }

        $requiredMeasurements = Item::getRequiredMeasurements($garmentType);
        $measurements_cm = [];

        foreach ($request->sizes as $size => $sizeData) {
            $measurements_cm[$size] = [];

            foreach ($requiredMeasurements as $measurement) {
                // Only add measurement if provided
                if (isset($sizeData[$measurement]) && $sizeData[$measurement] !== '') {
                    $measurements_cm[$size][$measurement] = $sizeData[$measurement];
                }
            }
        }

        return [
            'garment_type' => $garmentType,
            'measurements_cm' => $measurements_cm,
            'fit_characteristics' => [
                'fit_type' => $request->fit_type ?? 'regular',
                'ease' => $request->ease ?? 'standard',
                'stretch' => $request->stretch ?? 'none',
            ],
            'size_system' => $request->size_system ?? 'US',
            'last_updated' => now()->toISOString()
        ];
    }

    /**
     * Create mapping of category IDs to available garment types
     */
    private function getCategoryToGarmentTypesMapping()
    {
        $categories = Category::active()->ordered()->get();
        $mapping = [];

        foreach ($categories as $category) {
            $categorySlug = strtolower($category->name);
            $garmentTypes = Item::getGarmentTypesForCategory($categorySlug);
            $mapping[$category->id] = $garmentTypes;
        }

        return $mapping;
    }

    /**
     * API endpoint to get garment types for a category
     */
    public function getGarmentTypesByCategoryId($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json([]);
        }

        $categorySlug = strtolower($category->name);
        $garmentTypes = Item::getGarmentTypesForCategory($categorySlug);

        return response()->json($garmentTypes);
    }
}
