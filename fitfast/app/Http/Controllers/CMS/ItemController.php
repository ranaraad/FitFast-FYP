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
use Illuminate\Validation\ValidationException;

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

        // Initialize an empty item object for the create form
        $item = new Item();

        return view('cms.pages.items.create', compact(
            'stores',
            'categories',
            'garmentTypes',
            'standardSizes',
            'garmentTypesByCategory',
            'categoryToGarmentTypes',
            'item'
        ));
    }

    public function store(Request $request)
    {
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

        // Create the item
        $item = Item::create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->handleImageUploads($item, $request->file('images'));
        }

        return redirect()->route('cms.items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Validate item creation/update request
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
     * Validate variants array structure
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
     * Build variants array from color_variants input
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
     * Build aggregated data (color_variants, size_stock, total_stock) from variants
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

        return view('cms.pages.items.edit', compact(
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

        // Update the item
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
