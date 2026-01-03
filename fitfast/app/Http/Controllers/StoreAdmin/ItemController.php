<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
            ->with(['store', 'category', 'images'])
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

        // Initialize an empty item object for the create form
        $item = new Item();

        return view('cms.pages.store-admin.items.create', compact(
            'stores',
            'categories',
            'garmentTypes',
            'standardSizes',
            'garmentTypesByCategory',
            'categoryToGarmentTypes',
            'item' // Add item to compact
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

        // Use the same validation method as CMS controller
        $validated = $this->validateItemRequest($request);

        // Parse variants JSON if provided, otherwise build from color_variants
        if ($request->filled('variants')) {
            $variants = json_decode($request->variants, true);
            // Validate variants structure
            $this->validateVariants($variants);
        } else {
            // Build variants from color_variants array
            $variants = $this->buildVariantsFromColorVariants($request->color_variants);
        }

        // Build aggregated data from variants
        $aggregatedData = $this->buildAggregatedData($variants);

        // Update validated data
        $validated['variants'] = $variants;
        $validated['color_variants'] = $aggregatedData['color_variants'];
        $validated['size_stock'] = $aggregatedData['size_stock'];
        $validated['stock_quantity'] = $aggregatedData['total_stock'];

        // Build sizing_data (optional measurements)
        $validated['sizing_data'] = $this->buildSizingData($request);

        $validated['slug'] = Str::slug($validated['name']);

        // Create the item
        $item = Item::create($validated);

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $this->handleImageUploads($item, $request->file('images'));
        }

        return redirect()->route('store-admin.items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Validate item creation/update request (matching CMS controller)
     */
    private function validateItemRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'garment_type' => 'required|string|in:' . implode(',', array_keys(Item::GARMENT_TYPES)),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.name' => 'required|string|max:255',
            'color_variants.*.size_stock' => 'required|array',
            'color_variants.*.size_stock.*' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];

        // Variants is optional since we can build it from color_variants
        if ($request->filled('variants')) {
            $rules['variants'] = 'required|json';
        }

        // For update, images are optional
        if ($isUpdate) {
            $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        }

        return $request->validate($rules);
    }

    /**
     * Validate variants array structure (matching CMS controller)
     */
    private function validateVariants(array $variants)
    {
        if (empty($variants)) {
            throw ValidationException::withMessages([
                'variants' => ['Please add at least one color-size variant with stock.']
            ]);
        }

        foreach ($variants as $variant) {
            if (empty($variant['color']) || empty($variant['size'])) {
                throw ValidationException::withMessages([
                    'variants' => ['Each variant must have both color and size specified.']
                ]);
            }

            if (!isset($variant['stock']) || $variant['stock'] < 0) {
                throw ValidationException::withMessages([
                    'variants' => ['Stock quantity must be 0 or greater.']
                ]);
            }
        }
    }

    /**
     * Build variants array from color_variants input (matching CMS controller)
     */
    private function buildVariantsFromColorVariants(array $colorVariants)
    {
        $variants = [];

        foreach ($colorVariants as $colorData) {
            $colorName = $colorData['name'];
            $sizeStock = $colorData['size_stock'] ?? [];

            foreach ($sizeStock as $size => $stock) {
                if ($stock > 0) {
                    $variants[] = [
                        'color' => $colorName,
                        'size' => $size,
                        'stock' => $stock
                    ];
                }
            }
        }

        if (empty($variants)) {
            throw ValidationException::withMessages([
                'color_variants' => ['Please add stock for at least one color-size combination.']
            ]);
        }

        return $variants;
    }

    /**
     * Build aggregated data (color_variants, size_stock, total_stock) from variants (matching CMS controller)
     */
    private function buildAggregatedData(array $variants)
    {
        $colorVariants = [];
        $sizeStock = [];
        $totalStock = 0;

        foreach ($variants as $variant) {
            if (isset($variant['stock']) && $variant['stock'] > 0) {
                $color = $variant['color'];
                $size = $variant['size'];
                $stock = $variant['stock'];

                // Build color_variants (total per color)
                if (!isset($colorVariants[$color])) {
                    $colorVariants[$color] = [
                        'name' => $color,
                        'stock' => 0
                    ];
                }
                $colorVariants[$color]['stock'] += $stock;

                // Build size_stock (total per size)
                if (!isset($sizeStock[$size])) {
                    $sizeStock[$size] = 0;
                }
                $sizeStock[$size] += $stock;

                $totalStock += $stock;
            }
        }

        return [
            'color_variants' => $colorVariants,
            'size_stock' => $sizeStock,
            'total_stock' => $totalStock
        ];
    }

    public function show(Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            abort(403, 'Unauthorized access to this item.');
        }

        $item->load(['store', 'category', 'reviews.user', 'images']);

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

        // Prepare existing variants data for the view
        $existingVariants = $item->variants ?? [];
        $colorVariantsData = [];

        // Group variants by color for easier template rendering
        if (!empty($existingVariants)) {
            foreach ($existingVariants as $variant) {
                if (isset($variant['color']) && isset($variant['size']) && isset($variant['stock'])) {
                    $color = $variant['color'];
                    $size = $variant['size'];
                    $stock = $variant['stock'];

                    if (!isset($colorVariantsData[$color])) {
                        $colorVariantsData[$color] = [
                            'name' => $color,
                            'size_stock' => []
                        ];
                    }

                    $colorVariantsData[$color]['size_stock'][$size] = $stock;
                }
            }
        }

        $item->load('images');

        return view('cms.pages.store-admin.items.edit', compact(
            'item',
            'stores',
            'categories',
            'garmentTypes',
            'standardSizes',
            'garmentTypesByCategory',
            'categoryToGarmentTypes',
            'colorVariantsData' // Pass this to the view
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

        // Use the same validation method as CMS controller
        $validated = $this->validateItemRequest($request, true);

        // Parse variants JSON if provided, otherwise build from color_variants
        if ($request->filled('variants')) {
            $variants = json_decode($request->variants, true);
            // Validate variants structure
            $this->validateVariants($variants);
        } else {
            // Build variants from color_variants array
            $variants = $this->buildVariantsFromColorVariants($request->color_variants);
        }

        // Build aggregated data from variants
        $aggregatedData = $this->buildAggregatedData($variants);

        // Update validated data
        $validated['variants'] = $variants;
        $validated['color_variants'] = $aggregatedData['color_variants'];
        $validated['size_stock'] = $aggregatedData['size_stock'];
        $validated['stock_quantity'] = $aggregatedData['total_stock'];

        // Build sizing_data (optional measurements)
        $validated['sizing_data'] = $this->buildSizingData($request);

        $validated['slug'] = Str::slug($validated['name']);

        // Update the item
        $item->update($validated);

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $this->handleImageUploads($item, $request->file('images'));
        }

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

        // Delete associated images
        foreach ($item->images as $image) {
            $this->deleteImageFile($image->image_path);
        }

        $item->delete();

        return redirect()->route('store-admin.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * IMAGE MANAGEMENT METHODS
     */

    /**
     * Add multiple images to an item
     */
    public function addImages(Request $request, Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item'
            ], 403);
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $this->handleImageUploads($item, $request->file('images'));

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'images' => $item->images()->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set an image as primary
     */
    public function setPrimaryImage(Request $request, Item $item, ItemImage $image)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item'
            ], 403);
        }

        // Verify the image belongs to the item
        if ($image->item_id !== $item->id) {
            return response()->json([
                'success' => false,
                'message' => 'Image does not belong to this item'
            ], 403);
        }

        try {
            $item->setPrimaryImage($image);

            return response()->json([
                'success' => true,
                'message' => 'Primary image updated successfully',
                'primary_image' => $image
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder images
     */
    public function reorderImages(Request $request, Item $item)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item'
            ], 403);
        }

        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|exists:item_images,id'
        ]);

        try {
            $item->reorderImages($request->image_ids);

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an image
     */
    public function deleteImage(Request $request, Item $item, ItemImage $image)
    {
        $user = Auth::user();
        $userId = $user->id;
        $managedStoreIds = Store::where('user_id', $userId)->pluck('id');

        if (!$managedStoreIds->contains($item->store_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item'
            ], 403);
        }

        // Verify the image belongs to the item
        if ($image->item_id !== $item->id) {
            return response()->json([
                'success' => false,
                'message' => 'Image does not belong to this item'
            ], 403);
        }

        try {
            $imagePath = $image->image_path;
            $image->delete();

            // Delete the physical file
            $this->deleteImageFile($imagePath);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle image uploads for an item (matching CMS controller)
     */
    private function handleImageUploads(Item $item, array $images)
    {
        $uploadedImages = [];

        // Get the current highest order to continue from there
        $currentMaxOrder = $item->images()->max('order') ?? -1;

        foreach ($images as $index => $imageFile) {
            if ($imageFile->isValid()) {
                // Generate unique filename
                $filename = 'item_' . $item->id . '_' . time() . '_' . $index . '.' . $imageFile->getClientOriginalExtension();

                // Store image in public storage
                $path = $imageFile->storeAs('items/images', $filename, 'public');

                if ($path) {
                    $order = $currentMaxOrder + $index + 1;
                    $isPrimary = ($item->images()->count() === 0 && $index === 0); // Only primary if no images exist

                    $uploadedImages[] = [
                        'image_path' => $path,
                        'order' => $order,
                        'is_primary' => $isPrimary
                    ];
                }
            }
        }

        // Add images to the item
        if (!empty($uploadedImages)) {
            $item->images()->createMany($uploadedImages);
        }
    }

    /**
     * Delete physical image file (matching CMS controller)
     */
    private function deleteImageFile($imagePath)
    {
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
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
            ->with(['store', 'category', 'images'])
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
                'Image Count',
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
                    $item->images->count(),
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
