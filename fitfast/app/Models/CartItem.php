<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'item_id',
        'quantity',
        'selected_size',
        'selected_color',
        'item_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'item_price' => 'decimal:2',
    ];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the item that owns the cart item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate the total price for this cart item.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->item_price;
    }

    /**
     * Update quantity and recalculate price if needed.
     */
    public function updateQuantity(int $quantity): void
    {
        $this->update(['quantity' => $quantity]);
        $this->cart->updateTotal();
    }

    /**
     * Scope a query to only include items with specific size and color.
     */
    public function scopeWithVariants($query, $size = null, $color = null)
    {
        if ($size) {
            $query->where('selected_size', $size);
        }
        if ($color) {
            $query->where('selected_color', $color);
        }
        return $query;
    }
}
