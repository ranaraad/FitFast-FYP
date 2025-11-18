<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'image_path',
        'order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scope to get primary image
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Scope to order by order column
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
