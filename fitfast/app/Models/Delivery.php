<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'tracking_id',
        'carrier',
        'estimated_delivery',
        'status',
        'address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'estimated_delivery' => 'datetime',
    ];

    /**
     * Get the order that owns the delivery.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include pending deliveries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include active deliveries (not delivered or failed).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'failed']);
    }

    /**
     * Scope a query to only include delivered deliveries.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Check if delivery is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if delivery is in transit.
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['shipped', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Check if delivery is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if delivery has tracking information.
     */
    public function hasTracking(): bool
    {
        return !empty($this->tracking_id) && !empty($this->carrier);
    }

    /**
     * Update delivery status.
     */
    public function updateStatus(string $status): bool
    {
        $validStatuses = ['pending', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'failed'];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    /**
     * Mark delivery as shipped with tracking information.
     */
    public function markAsShipped(string $trackingId, string $carrier, ?string $estimatedDelivery = null): bool
    {
        return $this->update([
            'tracking_id' => $trackingId,
            'carrier' => $carrier,
            'estimated_delivery' => $estimatedDelivery,
            'status' => 'shipped',
        ]);
    }

    /**
     * Mark delivery as delivered.
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => 'delivered',
            'estimated_delivery' => now(),
        ]);
    }
}
