<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($delivery) {
            // Set default estimated delivery if not provided
            if (empty($delivery->estimated_delivery)) {
                $delivery->estimated_delivery = Carbon::now()->addDays(3);
            }
        });
    }

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
     * Scope a query to only include overdue deliveries.
     */
    public function scopeOverdue($query)
    {
        return $query->where('estimated_delivery', '<', now())
                    ->whereNotIn('status', ['delivered', 'failed']);
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
     * Check if delivery is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->estimated_delivery->isPast() && !$this->isCompleted();
    }

    /**
     * Check if delivery has tracking information.
     */
    public function hasTracking(): bool
    {
        return !empty($this->tracking_id) && !empty($this->carrier);
    }

    /**
     * Get the estimated delivery date formatted.
     */
    public function getEstimatedDeliveryFormatted(): string
    {
        return $this->estimated_delivery ? $this->estimated_delivery->format('M j, Y') : 'Not set';
    }

    /**
     * Get the days remaining until estimated delivery.
     */
    public function getDaysRemaining(): int
    {
        if (!$this->estimated_delivery || $this->isCompleted()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->estimated_delivery, false));
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
        $updateData = [
            'tracking_id' => $trackingId,
            'carrier' => $carrier,
            'status' => 'shipped',
        ];

        if ($estimatedDelivery) {
            $updateData['estimated_delivery'] = $estimatedDelivery;
        }

        return $this->update($updateData);
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
