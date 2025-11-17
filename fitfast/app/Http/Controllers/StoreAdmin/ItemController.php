<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $items = Item::whereIn('store_id', $managedStoreIds)
            ->when($request->store_id, function($query, $storeId) {
                return $query->where('store_id', $storeId);
            })
            ->when($request->low_stock, function($query) {
                return $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            })
            ->when($request->out_of_stock, function($query) {
                return $query->where('stock_quantity', 0);
            })
            ->when($request->category_id, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->with(['store', 'category'])
            ->orderBy('stock_quantity', 'asc') // Show low stock items first
            ->paginate(25);

        $stores = Store::where('user_id', $userId)->get();
        $categories = Category::where('is_active', true)->get();

        // Summary statistics
        $summary = [
            'total_items' => $items->total(),
            'low_stock_count' => Item::whereIn('store_id', $managedStoreIds)
                ->where('stock_quantity', '<', 10)
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock_count' => Item::whereIn('store_id', $managedStoreIds)
                ->where('stock_quantity', 0)
                ->count(),
            'total_stores' => $stores->count(),
        ];

        return view('cms.pages.store-admin.items.index', compact('items', 'stores', 'categories', 'summary'));
    }

    public function create()
    {
        $user = Auth::user();
        $userId = $user->id;
        $stores = Store::where('user_id', $userId)->where('status', 'active')->get();
        $categories = Category::active()->ordered()->get();

        // Get all garment types data (matching CMS controller)
        $garmentTypes = Item::GARMENT_TYPES;
        $standardSizes = Item::STANDARD_SIZES;
        $garmentTypesByCategory = Item::getGarmentTypesByCategory();

        // Get category to garment type mapping for JavaScript
        $categoryToGarmentTypes = $this->getCategoryToGarmentTypesMapping();

        return view('cms.pages.store-admin.items.create', compact(
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
        $user = Auth::user();
        $userId = $user->id;

        // Validate that the store belongs to the user
        $store = Store::where('id', $request->store_id)
            ->where('user_id', $userId)
            ->firstOrFail();

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

        // Validate stock consistency (matching CMS controller)
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

        // Build color variants structure (matching CMS controller)
        $colorVariants = [];
        foreach ($request->color_variants as $colorData) {
            $colorName = $colorData['name'];
            $colorVariants[$colorName] = [
                'name' => $colorName,
                'stock' => $colorData['stock']
            ];
        }
        $validated['color_variants'] = $colorVariants;

        // Build the proper sizing_data structure (matching CMS controller)
        $validated['sizing_data'] = $this->buildSizingData($request);

        // Build size_stock data
        $validated['size_stock'] = $request->size_stock;

        // Calculate total stock (should be the same from both sources)
        $validated['stock_quantity'] = $totalColorStock; // or $totalSizeStock, they should be equal

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        Item::create($validated);

        return redirect()->route('store-admin.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            abort(403, 'Unauthorized access to this item.');
        }

        $item->load(['store', 'category', 'reviews.user']);

        return view('cms.pages.store-admin.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            abort(403, 'Unauthorized access to this item.');
        }

        $stores = Store::where('user_id', $userId)->where('status', 'active')->get();
        $categories = Category::active()->ordered()->get();

        // Get all garment types data (matching CMS controller)
        $garmentTypes = Item::GARMENT_TYPES;
        $standardSizes = Item::STANDARD_SIZES;
        $garmentTypesByCategory = Item::getGarmentTypesByCategory();

        // Get category to garment type mapping for JavaScript
        $categoryToGarmentTypes = $this->getCategoryToGarmentTypesMapping();

        return view('cms.pages.store-admin.items.edit', compact(
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
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            abort(403, 'Unauthorized access to this item.');
        }

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

        // Build color variants structure (matching CMS controller)
        $colorVariants = [];
        foreach ($request->color_variants as $colorData) {
            $colorName = $colorData['name'];
            $colorVariants[$colorName] = [
                'name' => $colorName,
                'stock' => $colorData['stock']
            ];
        }
        $validated['color_variants'] = $colorVariants;

        // Build the proper sizing_data structure (matching CMS controller)
        $validated['sizing_data'] = $this->buildSizingData($request);

        // Build size_stock data
        $validated['size_stock'] = $request->size_stock;

        // Calculate total stock from color variants
        $totalStock = 0;
        foreach ($colorVariants as $colorData) {
            $totalStock += $colorData['stock'];
        }
        $validated['stock_quantity'] = $totalStock;

        // Handle image upload if present
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $item->update($validated);

        return redirect()->route('store-admin.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            abort(403, 'Unauthorized access to this item.');
        }

        // Check if item has orders
        if ($item->orders()->count() > 0) {
            return redirect()->route('store-admin.items.index')
                ->with('error', 'Cannot delete item that has associated orders.');
        }

        // Delete image if exists
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->route('store-admin.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    public function updateStock(Request $request, Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'action' => 'required|in:set,add,subtract'
        ]);

        $currentStock = $item->stock_quantity;

        switch ($request->action) {
            case 'set':
                $newStock = $request->stock_quantity;
                break;
            case 'add':
                $newStock = $currentStock + $request->stock_quantity;
                break;
            case 'subtract':
                $newStock = max(0, $currentStock - $request->stock_quantity);
                break;
        }

        $item->update(['stock_quantity' => $newStock]);

        return response()->json([
            'success' => true,
            'new_stock' => $newStock,
            'message' => 'Stock updated successfully'
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $items = Item::whereIn('store_id', $managedStoreIds)
            ->when($request->store_id, function($query, $storeId) {
                return $query->where('store_id', $storeId);
            })
            ->when($request->category_id, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
            })
            ->with(['store', 'category'])
            ->get();

        $fileName = 'items-export-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Store',
                'Category',
                'Price',
                'Stock Quantity',
                'Color Variants',
                'Sizes',
                'Status',
                'Created At',
                'Updated At'
            ]);

            // Add data rows
            foreach ($items as $item) {
                $colorVariants = $item->color_variants ?
                    implode(', ', array_keys($item->color_variants)) : 'N/A';

                $sizes = $item->size_stock ?
                    implode(', ', array_keys($item->size_stock)) : 'N/A';

                $status = $item->stock_quantity == 0 ? 'Out of Stock' :
                        ($item->stock_quantity < 10 ? 'Low Stock' : 'In Stock');

                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->description ?? 'N/A',
                    $item->store->name,
                    $item->category->name ?? 'N/A',
                    '$' . number_format($item->price, 2),
                    $item->stock_quantity,
                    $colorVariants,
                    $sizes,
                    $status,
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportLowStock(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        $lowStockItems = Item::whereIn('store_id', $managedStoreIds)
            ->where('stock_quantity', '<', 10)
            ->when($request->store_id, function($query, $storeId) {
                return $query->where('store_id', $storeId);
            })
            ->when($request->category_id, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->with(['store', 'category'])
            ->get();

        $fileName = 'low-stock-items-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($lowStockItems) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Store',
                'Category',
                'Price',
                'Current Stock',
                'Required Stock',
                'Stock Status',
                'Last Updated'
            ]);

            // Add data rows
            foreach ($lowStockItems as $item) {
                $requiredStock = max(0, 10 - $item->stock_quantity);
                $status = $item->stock_quantity == 0 ? 'Out of Stock' : 'Low Stock';

                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->store->name,
                    $item->category->name ?? 'N/A',
                    '$' . number_format($item->price, 2),
                    $item->stock_quantity,
                    $requiredStock,
                    $status,
                    $item->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build standardized sizing data structure (matching CMS controller)
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
     * Create mapping of category IDs to available garment types (matching CMS controller)
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
     * API endpoint to get garment types for a category (matching CMS controller)
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
