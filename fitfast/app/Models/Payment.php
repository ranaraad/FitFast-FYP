<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'payment_method_id',
        'amount',
        'transaction_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payment method that owns the payment.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment has transaction ID.
     */
    public function hasTransactionId(): bool
    {
        return !empty($this->transaction_id);
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(): bool
    {
        return $this->update([
            'status' => 'refunded',
        ]);
    }

    /**
     * Get payment status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'failed' => 'danger',
            'refunded' => 'warning',
            'processing' => 'info',
            default => 'secondary',
        };
    }
}
