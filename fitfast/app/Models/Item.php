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
        'name',
        'description',
        'price',
        'sizing_data',
        'category',
        'color',
        'stock_quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sizing_data' => 'array',
        'stock_quantity' => 'integer',
    ];

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
     * Get the order items for the item.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if item is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
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
}
