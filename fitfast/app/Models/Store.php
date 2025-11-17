<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'logo',
        'banner_image',
        'contact_info',
        'address',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'contact_info' => 'array', // If storing as JSON
    ];

    /**
     * Get the user that owns the store.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the store.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the orders for the store.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the low stock items for the store.
     */
    public function low_stock_items()
    {
        return $this->hasMany(Item::class)->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
    }

    /**
     * Get the out of stock items for the store.
     */
    public function out_of_stock_items()
    {
        return $this->hasMany(Item::class)->where('stock_quantity', 0);
    }

    /**
     * Get the critical stock items for the store.
     */
    public function critical_stock_items()
    {
        return $this->hasMany(Item::class)->where('stock_quantity', '<', 5)->where('stock_quantity', '>', 0);
    }
}
