<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'rating',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item that was reviewed.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope a query to only include reviews with a given rating or higher.
     */
    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to order by latest reviews.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the review has a comment.
     */
    public function hasComment(): bool
    {
        return !empty($this->comment);
    }
}
