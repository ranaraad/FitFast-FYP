<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'sizing_data',
        'color_variants',
        'stock_quantity',
        'garment_type',
        'size_stock',
        'variants', // Added for color-size variant support
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sizing_data' => 'array',
        'stock_quantity' => 'integer',
        'size_stock' => 'array',
        'color_variants' => 'array',
        'variants' => 'array',
    ];

    /**
     * GARMENT TYPES - These define the STRUCTURE of sizing_data
     * Each garment type has different measurement requirements
     */
    public const GARMENT_TYPES = [
        't_shirt' => [
            'name' => 'Standard T-Shirt',
            'category' => 't-shirts',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'fitted_shirt' => [
            'name' => 'Fitted Shirt',
            'category' => 'shirts',
            'measurements' => ['chest_circumference', 'waist_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'dress_shirt' => [
            'name' => 'Dress Shirt',
            'category' => 'shirts',
            'measurements' => ['chest_circumference', 'waist_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'collar_size']
        ],
        'slim_pants' => [
            'name' => 'Slim Pants',
            'category' => 'pants',
            'measurements' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening']
        ],
        'regular_pants' => [
            'name' => 'Regular Pants',
            'category' => 'pants',
            'measurements' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening']
        ],
        'regular_jeans' => [
            'name' => 'Regular Jeans',
            'category' => 'jeans',
            'measurements' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening', 'rise']
        ],
        'slim_jeans' => [
            'name' => 'Slim Jeans',
            'category' => 'jeans',
            'measurements' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening', 'rise']
        ],
        'casual_shorts' => [
            'name' => 'Casual Shorts',
            'category' => 'shorts',
            'measurements' => ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference', 'leg_opening']
        ],
        'a_line_dress' => [
            'name' => 'A-Line Dress',
            'category' => 'dresses',
            'measurements' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem']
        ],
        'bodycon_dress' => [
            'name' => 'Bodycon Dress',
            'category' => 'dresses',
            'measurements' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length']
        ],
        'maxi_dress' => [
            'name' => 'Maxi Dress',
            'category' => 'dresses',
            'measurements' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem']
        ],
            'sun_dress' => [
            'name' => 'Sun Dress',
            'category' => 'dresses',
            'measurements' => ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem']
        ],
        'pencil_skirt' => [
            'name' => 'Pencil Skirt',
            'category' => 'skirts',
            'measurements' => ['waist_circumference', 'hips_circumference', 'skirt_length']
        ],
        'a_line_skirt' => [
            'name' => 'A-Line Skirt',
            'category' => 'skirts',
            'measurements' => ['waist_circumference', 'hips_circumference', 'skirt_length']
        ],
        'bomber_jacket' => [
            'name' => 'Bomber Jacket',
            'category' => 'jackets',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'bicep_circumference']
        ],
        'denim_jacket' => [
            'name' => 'Denim Jacket',
            'category' => 'jackets',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'trench_coat' => [
            'name' => 'Trench Coat',
            'category' => 'coats',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'wool_coat' => [
            'name' => 'Wool Coat',
            'category' => 'coats',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'crewneck_sweater' => [
            'name' => 'Crewneck Sweater',
            'category' => 'sweaters',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'v_neck_sweater' => [
            'name' => 'V-Neck Sweater',
            'category' => 'sweaters',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width']
        ],
        'pullover_hoodie' => [
            'name' => 'Pullover Hoodie',
            'category' => 'hoodies',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height']
        ],
        'zip_hoodie' => [
            'name' => 'Zip-Up Hoodie',
            'category' => 'hoodies',
            'measurements' => ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height']
        ],
        'yoga_pants' => [
            'name' => 'Yoga Pants',
            'category' => 'activewear',
            'measurements' => ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference']
        ],
        'training_shorts' => [
            'name' => 'Training Shorts',
            'category' => 'activewear',
            'measurements' => ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference']
        ],
        'bikini_top' => [
            'name' => 'Bikini Top',
            'category' => 'swimwear',
            'measurements' => ['chest_circumference', 'underbust_circumference', 'cup_size']
        ],
        'swim_trunks' => [
            'name' => 'Swim Trunks',
            'category' => 'swimwear',
            'measurements' => ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference']
        ],
        'briefs' => [
            'name' => 'Briefs',
            'category' => 'underwear',
            'measurements' => ['waist_circumference', 'hips_circumference']
        ],
        'boxers' => [
            'name' => 'Boxers',
            'category' => 'underwear',
            'measurements' => ['waist_circumference', 'hips_circumference', 'short_length']
        ],
        'ankle_socks' => [
            'name' => 'Ankle Socks',
            'category' => 'socks',
            'measurements' => ['foot_length', 'calf_circumference']
        ],
        'crew_socks' => [
            'name' => 'Crew Socks',
            'category' => 'socks',
            'measurements' => ['foot_length', 'calf_circumference', 'sock_height']
        ],
        'sneakers' => [
            'name' => 'Sneakers',
            'category' => 'shoes',
            'measurements' => ['foot_length', 'foot_width']
        ],
        'dress_shoes' => [
            'name' => 'Dress Shoes',
            'category' => 'shoes',
            'measurements' => ['foot_length', 'foot_width']
        ],
        'backpack' => [
            'name' => 'Backpack',
            'category' => 'bags',
            'measurements' => ['bag_width', 'bag_height', 'bag_depth', 'strap_length']
        ],
        'tote_bag' => [
            'name' => 'Tote Bag',
            'category' => 'bags',
            'measurements' => ['bag_width', 'bag_height', 'bag_depth', 'handle_length']
        ],
        'necklace' => [
            'name' => 'Necklace',
            'category' => 'jewelry',
            'measurements' => ['chain_length']
        ],
        'bracelet' => [
            'name' => 'Bracelet',
            'category' => 'jewelry',
            'measurements' => ['bracelet_circumference']
        ],
        'baseball_cap' => [
            'name' => 'Baseball Cap',
            'category' => 'hats',
            'measurements' => ['head_circumference', 'brim_width']
        ],
        'beanie' => [
            'name' => 'Beanie',
            'category' => 'hats',
            'measurements' => ['head_circumference', 'hat_height']
        ]
    ];

    /**
     * STANDARD SIZES - For consistent sizing across all items
     */
    public const STANDARD_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    /**
     * RELATIONSHIPS
     */

    /**
     * The users that have this item (many-to-many relationship)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'item_user')
                    ->using(ItemUser::class)
                    ->withTimestamps();
    }

    /**
     * Get the store that owns the item.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category that owns the item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for the item.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the reviews for the item.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the cart items for the item.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the images for the item.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('order');
    }

    /**
     * IMAGE MANAGEMENT METHODS
     */

    /**
     * Accessor for primary image
     */
    public function getPrimaryImageAttribute()
    {
        return $this->images->where('is_primary', true)->first()
            ?? $this->images->first();
    }

    /**
     * Method to set primary image
     */
    public function setPrimaryImage(ItemImage $image): void
    {
        // Verify the image belongs to this item
        if ($image->item_id !== $this->id) {
            throw new \InvalidArgumentException('Image does not belong to this item');
        }

        // Remove primary status from all other images
        $this->images()->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);
    }

    /**
     * Method to add an image
     */
    public function addImage(string $imagePath, bool $isPrimary = false, int $order = 0): ItemImage
    {
        // If this is set as primary, remove primary status from others
        if ($isPrimary) {
            $this->images()->update(['is_primary' => false]);
        }

        // If no images exist and this isn't explicitly set as primary, make it primary
        if ($this->images()->count() === 0 && !$isPrimary) {
            $isPrimary = true;
        }

        return $this->images()->create([
            'image_path' => $imagePath,
            'order' => $order,
            'is_primary' => $isPrimary,
        ]);
    }

    /**
     * Method to add multiple images
     */
    public function addImages(array $images): void
    {
        $hasPrimary = false;

        foreach ($images as $index => $imageData) {
            $isPrimary = $imageData['is_primary'] ?? ($index === 0 && !$hasPrimary);

            if ($isPrimary) {
                $hasPrimary = true;
            }

            $this->addImage(
                $imageData['image_path'],
                $isPrimary,
                $imageData['order'] ?? $index
            );
        }
    }

    /**
     * Method to reorder images
     */
    public function reorderImages(array $imageIds): void
    {
        foreach ($imageIds as $order => $imageId) {
            $this->images()
                ->where('id', $imageId)
                ->update(['order' => $order]);
        }
    }

    /**
     * Check if item has images
     */
    public function hasImages(): bool
    {
        return $this->images()->exists();
    }

    /**
     * Get image count
     */
    public function getImageCountAttribute(): int
    {
        return $this->images()->count();
    }

    /**
     * STOCK MANAGEMENT METHODS - NEW COLOR-SIZE VARIANT SYSTEM
     */

    /**
     * Get variant key for storage
     */
    private function getVariantKey($color, $size)
    {
        return strtolower($color) . '_' . strtoupper($size);
    }

    /**
     * Update aggregated stock data from variants
     */
    public function updateAggregatedStock()
    {
        $variants = $this->variants ?? [];
        $colorVariants = [];
        $sizeStock = [];
        $totalStock = 0;

        foreach ($variants as $variant) {
            if (isset($variant['stock']) && $variant['stock'] > 0) {
                $color = $variant['color'];
                $size = $variant['size'];
                $stock = $variant['stock'];

                // Update color variants
                if (!isset($colorVariants[$color])) {
                    $colorVariants[$color] = [
                        'name' => $color,
                        'stock' => 0
                    ];
                }
                $colorVariants[$color]['stock'] += $stock;

                // Update size stock
                if (!isset($sizeStock[$size])) {
                    $sizeStock[$size] = 0;
                }
                $sizeStock[$size] += $stock;

                $totalStock += $stock;
            }
        }

        $this->color_variants = $colorVariants;
        $this->size_stock = $sizeStock;
        $this->stock_quantity = $totalStock;
    }

    /**
     * Get stock for a specific color-size variant
     */
    public function getVariantStock($color, $size)
    {
        $variants = $this->variants ?? [];
        $key = $this->getVariantKey($color, $size);

        // First check for the key-based entry (like "blue_M")
        if (isset($variants[$key])) {
            return $variants[$key]['stock'] ?? 0;
        }

        // If not found, search through numeric indexed entries
        foreach ($variants as $variant) {
            if (isset($variant['color'], $variant['size'], $variant['stock'])) {
                // Case-insensitive comparison
                if (strtolower($variant['color']) === strtolower($color) &&
                    strtoupper($variant['size']) === strtoupper($size)) {
                    return $variant['stock'];
                }
            }
        }

        return 0;
    }

    /**
     * Set stock for a specific color-size variant
     */
    public function setVariantStock($color, $size, $quantity)
    {
        $variants = $this->variants ?? [];
        $key = $this->getVariantKey($color, $size);

        if ($quantity > 0) {
            // Check if variant already exists (numeric or key-based)
            $found = false;
            foreach ($variants as $index => $variant) {
                if (isset($variant['color'], $variant['size'])) {
                    if (strtolower($variant['color']) === strtolower($color) &&
                        strtoupper($variant['size']) === strtoupper($size)) {
                        // Update existing variant
                        if (is_string($index)) {
                            // It's a key-based entry like "blue_M"
                            $variants[$index]['stock'] = max(0, $quantity);
                        } else {
                            // It's a numeric indexed entry
                            $variants[$index]['stock'] = max(0, $quantity);
                        }
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                // Create new variant with key-based entry
                $variants[$key] = [
                    'color' => $color,
                    'size' => $size,
                    'stock' => max(0, $quantity)
                ];
            }
        } else {
            // Remove variant if quantity is 0 or less
            foreach ($variants as $index => $variant) {
                if (isset($variant['color'], $variant['size'])) {
                    if (strtolower($variant['color']) === strtolower($color) &&
                        strtoupper($variant['size']) === strtoupper($size)) {
                        unset($variants[$index]);
                        break;
                    }
                }
            }
        }

        $this->variants = $variants;

        // Update aggregated data
        $this->updateAggregatedStock();
    }

    /**
     * Decrease stock for a variant
     */
    public function decreaseVariantStock($color, $size, $quantity = 1): bool
    {
        $currentStock = $this->getVariantStock($color, $size);
        if ($currentStock >= $quantity) {
            $this->setVariantStock($color, $size, $currentStock - $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock for a variant
     */
    public function increaseVariantStock($color, $size, $quantity = 1): void
    {
        $currentStock = $this->getVariantStock($color, $size);
        $this->setVariantStock($color, $size, $currentStock + $quantity);
    }

    /**
     * Get available variants (with stock > 0)
     */
    public function getAvailableVariantsAttribute()
    {
        $available = [];
        $variants = $this->variants ?? [];

        foreach ($variants as $variant) {
            if (($variant['stock'] ?? 0) > 0) {
                $available[] = $variant;
            }
        }

        return $available;
    }

    /**
     * Get variants grouped by color
     */
    public function getVariantsByColorAttribute()
    {
        $grouped = [];
        $variants = $this->variants ?? [];

        foreach ($variants as $variant) {
            if (($variant['stock'] ?? 0) > 0) {
                $color = $variant['color'];
                if (!isset($grouped[$color])) {
                    $grouped[$color] = [];
                }
                $grouped[$color][] = $variant;
            }
        }

        return $grouped;
    }

    /**
     * Get variants grouped by size
     */
    public function getVariantsBySizeAttribute()
    {
        $grouped = [];
        $variants = $this->variants ?? [];

        foreach ($variants as $variant) {
            if (($variant['stock'] ?? 0) > 0) {
                $size = $variant['size'];
                if (!isset($grouped[$size])) {
                    $grouped[$size] = [];
                }
                $grouped[$size][] = $variant;
            }
        }

        return $grouped;
    }

    /**
     * DEPRECATED/LEGACY METHODS - For backward compatibility
     * These should be phased out as we move to the new variant system
     */

    /**
     * Get stock quantity for a specific size (Legacy)
     */
    public function getSizeStock($size)
    {
        $sizeStock = $this->size_stock ?? [];
        return $sizeStock[$size] ?? 0;
    }

    /**
     * Get available sizes (sizes with stock > 0) (Legacy)
     */
    public function getAvailableSizesAttribute()
    {
        $availableSizes = [];
        $sizeStock = $this->size_stock ?? [];

        foreach ($sizeStock as $size => $quantity) {
            if ($quantity > 0) {
                $availableSizes[] = $size;
            }
        }

        return $availableSizes;
    }

    /**
     * Check if a specific size is in stock (Legacy)
     */
    public function isSizeInStock($size)
    {
        return $this->getSizeStock($size) > 0;
    }

    /**
     * Get available colors with stock information (Legacy)
     */
    public function getAvailableColorsAttribute()
    {
        $colors = $this->color_variants ?? [];
        $availableColors = [];

        foreach ($colors as $color => $colorData) {
            if ($this->getColorStock($color) > 0) {
                $availableColors[$color] = $colorData['name'] ?? $color;
            }
        }

        return $availableColors;
    }

    /**
     * Get stock for a specific color (Legacy)
     */
    public function getColorStock($color)
    {
        $colorVariants = $this->color_variants ?? [];
        return $colorVariants[$color]['stock'] ?? 0;
    }

    /**
     * Check if a color is available (Legacy)
     */
    public function isColorInStock($color, $quantity = 1)
    {
        return $this->getColorStock($color) >= $quantity;
    }

    /**
     * Get default color (first available color) (Legacy)
     */
    public function getDefaultColorAttribute()
    {
        $availableColors = $this->available_colors;
        return !empty($availableColors) ? array_key_first($availableColors) : null;
    }

    /**
     * Calculate total stock (Legacy - now uses aggregated data)
     */
    public function calculateTotalStock()
    {
        return $this->stock_quantity;
    }

    /**
     * Decrease stock for a specific color (Legacy)
     */
    public function decreaseColorStock($color, $quantity = 1): bool
    {
        // Try to use variant system first
        $availableSizes = $this->available_sizes;
        foreach ($availableSizes as $size) {
            if ($this->getVariantStock($color, $size) >= $quantity) {
                return $this->decreaseVariantStock($color, $size, $quantity);
            }
        }

        // Fall back to legacy method
        $currentStock = $this->getColorStock($color);
        if ($currentStock >= $quantity) {
            $colorVariants = $this->color_variants ?? [];
            $colorVariants[$color]['stock'] = $currentStock - $quantity;
            $this->color_variants = $colorVariants;
            $this->stock_quantity = $this->calculateTotalStock();
            return $this->save();
        }
        return false;
    }

    /**
     * Increase stock for a specific color (Legacy)
     */
    public function increaseColorStock($color, $quantity = 1): void
    {
        // For legacy support, we need to distribute to a default size
        $defaultSize = 'M'; // Default to medium
        $this->increaseVariantStock($color, $defaultSize, $quantity);
    }

    /**
     * GARMENT TYPE METHODS
     */

    /**
     * Get available garment types for a specific category slug
     */
    public static function getGarmentTypesForCategory($categorySlug)
    {
        $garmentTypes = [];

        foreach (self::GARMENT_TYPES as $key => $garment) {
            if ($garment['category'] === $categorySlug) {
                $garmentTypes[$key] = $garment['name'];
            }
        }

        return !empty($garmentTypes) ? $garmentTypes : ['t_shirt' => 'Standard T-Shirt'];
    }

    /**
     * Get all garment types grouped by category
     */
    public static function getGarmentTypesByCategory()
    {
        $grouped = [];

        foreach (self::GARMENT_TYPES as $key => $garment) {
            $category = $garment['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = $garment['name'];
        }

        return $grouped;
    }

    /**
     * Get display name for a garment type
     */
    public static function getGarmentTypeName($garmentType)
    {
        return self::GARMENT_TYPES[$garmentType]['name'] ?? 'Unknown Garment Type';
    }

    /**
     * Get required measurements for a garment type
     */
    public static function getRequiredMeasurements($garmentType)
    {
        return self::GARMENT_TYPES[$garmentType]['measurements'] ?? [];
    }

    /**
     * Validate if sizing data matches garment type requirements
     */
    public function validateSizingData()
    {
        if (!$this->garment_type || !isset(self::GARMENT_TYPES[$this->garment_type])) {
            return false;
        }

        $requiredMeasurements = self::getRequiredMeasurements($this->garment_type);
        $actualMeasurements = $this->garment_measurements;

        foreach ($requiredMeasurements as $measurement) {
            if (!isset($actualMeasurements[$measurement])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get garment type display name
     */
    public function getGarmentTypeDisplayNameAttribute()
    {
        return self::getGarmentTypeName($this->garment_type);
    }

    /**
     * Get the measurements from sizing_data
     */
    public function getGarmentMeasurementsAttribute()
    {
        return $this->sizing_data['measurements_cm'] ?? [];
    }

    /**
     * Check if item has proper sizing data for AI
     */
    public function hasAISizingData()
    {
        return !empty($this->garment_type) &&
               !empty($this->sizing_data['measurements_cm']) &&
               $this->validateSizingData();
    }

    /**
     * Get category-appropriate garment types
     */
    public function getAvailableGarmentTypesAttribute()
    {
        if ($this->category) {
            return self::getGarmentTypesForCategory($this->category->slug);
        }

        return [];
    }

    /**
     * SCOPES
     */

    /**
     * Scope a query to filter by garment type
     */
    public function scopeByGarmentType($query, $garmentType)
    {
        return $query->where('garment_type', $garmentType);
    }

    /**
     * Scope a query to filter by category slug
     */
    public function scopeByCategorySlug($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope a query to include only items with AI sizing data
     */
    public function scopeWithAISizing($query)
    {
        return $query->whereNotNull('garment_type')
                    ->whereNotNull('sizing_data');
    }

    /**
     * Scope a query to only include items in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by category type.
     */
    public function scopeByCategoryType($query, $type)
    {
        return $query->whereHas('category', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Scope a query to include items with images
     */
    public function scopeWithImages($query)
    {
        return $query->whereHas('images');
    }

    /**
     * Scope a query to include items without images
     */
    public function scopeWithoutImages($query)
    {
        return $query->whereDoesntHave('images');
    }

    /**
     * OTHER METHODS
     */

    /**
     * Check if item is in stock (any size)
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Decrease stock quantity (Legacy - use variant methods instead)
     */
    public function decreaseStock(int $quantity = 1): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock quantity (Legacy - use variant methods instead)
     */
    public function increaseStock(int $quantity = 1): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Calculate the average rating for the item.
     */
    public function averageRating(): float
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    /**
     * Get the number of reviews for the item.
     */
    public function reviewCount(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get stock status for a size (Legacy)
     */
    public function getSizeStockStatus($size): string
    {
        $stock = $this->getSizeStock($size);

        if ($stock > 10) {
            return 'in_stock';
        } elseif ($stock > 0) {
            return 'low_stock';
        } else {
            return 'out_of_stock';
        }
    }

    /**
     * Get measurement description for display
     */
    public function getMeasurementDescription($measurement)
    {
        $descriptions = [
            'chest_circumference' => 'Around fullest part of chest',
            'waist_circumference' => 'Around natural waistline',
            'hips_circumference' => 'Around fullest part of hips',
            'garment_length' => 'From shoulder to bottom hem',
            'sleeve_length' => 'From shoulder seam to cuff',
            'shoulder_width' => 'Shoulder seam to shoulder seam',
            'inseam_length' => 'From crotch to bottom of leg',
            'thigh_circumference' => 'Around fullest part of thigh',
            'leg_opening' => 'Circumference of leg opening',
            'rise' => 'From crotch to top of waistband',
            'collar_size' => 'Around neck where collar sits',
            'short_length' => 'From waist to bottom of shorts',
            'dress_length' => 'From shoulder to bottom hem',
            'shoulder_to_hem' => 'From shoulder to hem of dress',
            'skirt_length' => 'From waist to bottom hem',
            'bicep_circumference' => 'Around fullest part of bicep',
            'hood_height' => 'From neckline to top of hood',
            'underbust_circumference' => 'Around chest under bust',
            'cup_size' => 'Bra cup size',
            'foot_length' => 'Length of foot',
            'foot_width' => 'Width of foot',
            'calf_circumference' => 'Around fullest part of calf',
            'sock_height' => 'Height from ankle',
            'bag_width' => 'Width of bag',
            'bag_height' => 'Height of bag',
            'bag_depth' => 'Depth of bag',
            'strap_length' => 'Length of strap',
            'handle_length' => 'Length of handle',
            'chain_length' => 'Length of chain',
            'bracelet_circumference' => 'Around wrist',
            'head_circumference' => 'Around head',
            'brim_width' => 'Width of hat brim',
            'hat_height' => 'Height of hat'
        ];

        return $descriptions[$measurement] ?? 'Garment measurement';
    }

    /**
     * SAFE STOCK METHODS WITH RACE CONDITION PROTECTION
     */

    public function safeDecreaseStock(int $quantity = 1, $color = null, $size = null): bool
    {
        // If both color and size are provided, use variant system
        if ($color && $size) {
            return $this->safeDecreaseVariantStock($color, $size, $quantity);
        }

        // If only color is provided, use color-based legacy system
        if ($color && !empty($this->color_variants)) {
            return $this->safeDecreaseColorStock($color, $quantity);
        }

        // For regular stock - atomic operation that prevents race conditions
        $affected = DB::table('items')
            ->where('id', $this->id)
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity);

        // Refresh the model to get updated stock quantity
        if ($affected > 0) {
            $this->refresh();
            return true;
        }

        return false;
    }

    /**
     * SAFELY decrease variant stock - prevents overselling
     */
    public function safeDecreaseVariantStock($color, $size, $quantity = 1): bool
    {
        $variants = $this->variants ?? [];
        $key = $this->getVariantKey($color, $size);

        if (!isset($variants[$key])) {
            return false;
        }

        $currentStock = $variants[$key]['stock'] ?? 0;

        if ($currentStock < $quantity) {
            return false;
        }

        // Update variant stock
        $variants[$key]['stock'] = $currentStock - $quantity;
        if ($variants[$key]['stock'] <= 0) {
            unset($variants[$key]);
        }

        // Update the database with the new variants and recalculate aggregates
        $this->variants = $variants;
        $this->updateAggregatedStock();

        return $this->save();
    }

    /**
     * SAFELY decrease color stock - prevents overselling (Legacy)
     */
    public function safeDecreaseColorStock($color, $quantity = 1): bool
    {
        $colorVariants = $this->color_variants ?? [];

        if (!isset($colorVariants[$color])) {
            return false;
        }

        $currentStock = $colorVariants[$color]['stock'] ?? 0;

        if ($currentStock < $quantity) {
            return false;
        }

        // Update color stock
        $colorVariants[$color]['stock'] = $currentStock - $quantity;
        $this->color_variants = $colorVariants;

        // Update total stock quantity
        $this->stock_quantity = $this->calculateTotalStock();

        return $this->save();
    }

    /**
     * Check if item can fulfill order (has enough stock)
     */
    public function canFulfillOrder($quantity, $color = null, $size = null): bool
    {
        // If both color and size are provided, check variant stock
        if ($color && $size) {
            return $this->getVariantStock($color, $size) >= $quantity;
        }

        // If only color is provided, check color stock
        if ($color && !empty($this->color_variants)) {
            return $this->getColorStock($color) >= $quantity;
        }

        // Otherwise check total stock
        return $this->stock_quantity >= $quantity;
    }

    /**
     * SAFELY increase stock (for returns/cancellations)
     */
    public function safeIncreaseStock(int $quantity = 1, $color = null, $size = null): bool
    {
        // If both color and size are provided, use variant system
        if ($color && $size) {
            return $this->safeIncreaseVariantStock($color, $size, $quantity);
        }

        // If only color is provided, use color-based legacy system
        if ($color && !empty($this->color_variants)) {
            return $this->safeIncreaseColorStock($color, $quantity);
        }

        $this->increment('stock_quantity', $quantity);
        return true;
    }

    /**
     * SAFELY increase variant stock
     */
    public function safeIncreaseVariantStock($color, $size, $quantity = 1): bool
    {
        $variants = $this->variants ?? [];
        $key = $this->getVariantKey($color, $size);

        if (!isset($variants[$key])) {
            $variants[$key] = [
                'color' => $color,
                'size' => $size,
                'stock' => 0
            ];
        }

        $currentStock = $variants[$key]['stock'] ?? 0;
        $variants[$key]['stock'] = $currentStock + $quantity;
        $this->variants = $variants;

        // Update aggregated data
        $this->updateAggregatedStock();

        return $this->save();
    }

    /**
     * SAFELY increase color stock (Legacy)
     */
    public function safeIncreaseColorStock($color, $quantity = 1): bool
    {
        $colorVariants = $this->color_variants ?? [];

        if (!isset($colorVariants[$color])) {
            $colorVariants[$color] = ['name' => $color, 'stock' => 0];
        }

        $currentStock = $colorVariants[$color]['stock'] ?? 0;
        $colorVariants[$color]['stock'] = $currentStock + $quantity;
        $this->color_variants = $colorVariants;

        // Update total stock quantity
        $this->stock_quantity = $this->calculateTotalStock();

        return $this->save();
    }

    // In Item model, add this method:
    public function getVariantStockDebug($color, $size)
    {
        $variants = $this->variants ?? [];
        $key = $this->getVariantKey($color, $size);

        Log::info('Variant stock debug', [
            'color' => $color,
            'size' => $size,
            'key' => $key,
            'variants' => $variants,
            'found' => $variants[$key] ?? 'not found'
        ]);

        return $variants[$key]['stock'] ?? 0;
    }
}
