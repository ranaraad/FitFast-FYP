<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sizing_data' => 'array',
        'stock_quantity' => 'integer',
        'size_stock' => 'array',
        'color_variants' => 'array',
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
     * Get stock quantity for a specific size
     */
    public function getSizeStock($size)
    {
        $sizeStock = $this->size_stock ?? [];
        return $sizeStock[$size] ?? 0;
    }

    /**
     * Set stock quantity for a specific size
     */
    public function setSizeStock($size, $quantity)
    {
        $sizeStock = $this->size_stock ?? [];
        $sizeStock[$size] = max(0, $quantity);
        $this->size_stock = $sizeStock;

        // Update total stock quantity
        $this->stock_quantity = array_sum($sizeStock);
    }

    /**
     * Get available sizes (sizes with stock > 0)
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
     * Check if a specific size is in stock
     */
    public function isSizeInStock($size)
    {
        return $this->getSizeStock($size) > 0;
    }

    /**
     * Decrease stock for a specific size
     */
    public function decreaseSizeStock($size, $quantity = 1)
    {
        $currentStock = $this->getSizeStock($size);
        if ($currentStock >= $quantity) {
            $this->setSizeStock($size, $currentStock - $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock for a specific size
     */
    public function increaseSizeStock($size, $quantity = 1)
    {
        $currentStock = $this->getSizeStock($size);
        $this->setSizeStock($size, $currentStock + $quantity);
    }

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
     * Check if item is in stock (any size)
     */
    public function isInStock(): bool
    {
        return $this->calculateTotalStock() > 0;
    }

    /**
     * Scope a query to only include items in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Decrease stock quantity.
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
     * Increase stock quantity.
     */
    public function increaseStock(int $quantity = 1): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Get the reviews for the item.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
     * Get the cart items for the item.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
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
     * Get stock status for a size
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
     * Get available colors with stock information
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
     * Get stock for a specific color
     */
    public function getColorStock($color)
    {
        $colorVariants = $this->color_variants ?? [];
        return $colorVariants[$color]['stock'] ?? 0;
    }

    /**
     * Set stock for a specific color
     */
    public function setColorStock($color, $quantity)
    {
        $colorVariants = $this->color_variants ?? [];

        if (!isset($colorVariants[$color])) {
            $colorVariants[$color] = ['name' => $color, 'stock' => 0];
        }

        $colorVariants[$color]['stock'] = max(0, $quantity);
        $this->color_variants = $colorVariants;

        // Update total stock quantity
        $this->stock_quantity = $this->calculateTotalStock();
    }

    /**
     * Get stock for a specific color and size combination
     */
    public function getVariantStock($color, $size = null)
    {
        // For now, we'll use color stock. In a real system, you might want
        // to track stock per color-size combination
        return $this->getColorStock($color);
    }

    /**
     * Check if a color is available
     */
    public function isColorInStock($color, $quantity = 1)
    {
        return $this->getColorStock($color) >= $quantity;
    }

    /**
     * Get default color (first available color)
     */
    public function getDefaultColorAttribute()
    {
        $availableColors = $this->available_colors;
        return !empty($availableColors) ? array_key_first($availableColors) : null;
    }

    /**
     * Calculate total stock from all color variants
     */
    public function calculateTotalStock()
    {
        $colorVariants = $this->color_variants ?? [];
        $total = 0;

        foreach ($colorVariants as $colorData) {
            $total += $colorData['stock'] ?? 0;
        }

        return $total;
    }

    /**
     * Decrease stock for a specific color
     */
    public function decreaseColorStock($color, $quantity = 1)
    {
        $currentStock = $this->getColorStock($color);
        if ($currentStock >= $quantity) {
            $this->setColorStock($color, $currentStock - $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock for a specific color
     */
    public function increaseColorStock($color, $quantity = 1)
    {
        $currentStock = $this->getColorStock($color);
        $this->setColorStock($color, $currentStock + $quantity);
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
}
