<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'cart_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'cart_total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate and update the cart total.
     */
    public function updateTotal(): void
    {
        $total = $this->cartItems->sum(function ($cartItem) {
            return $cartItem->quantity * $cartItem->item_price;
        });

        $this->update(['cart_total' => $total]);
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->cartItems->sum('quantity');
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->cartItems->isEmpty();
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        $this->cartItems()->delete();
        $this->update(['cart_total' => 0]);
    }

    public function scopeWithItems($query)
    {
        return $query->whereHas('cartItems');
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->cart_total, 2);
    }

    public function getLastActivityAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

}
