<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['store', 'category', 'users', 'images'])->get();
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
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

        $item = Item::create($validated);

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $this->handleImageUploads($item, $request->file('images'));
        }

        return redirect()->route('cms.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['store', 'category', 'users', 'images']);
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

        $item->load('images');

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
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            $this->handleImageUploads($item, $request->file('images'));
        }

        return redirect()->route('cms.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        // Delete associated images
        foreach ($item->images as $image) {
            $this->deleteImageFile($image->image_path);
        }

        $item->delete();

        return redirect()->route('cms.items.index')
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
     * Handle image uploads for an item
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
     * Delete physical image file
     */
    private function deleteImageFile($imagePath)
    {
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
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
