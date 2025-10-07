<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'details',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments for the payment method.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Set the payment method details (encrypt before saving).
     */
    public function setDetailsAttribute($value)
    {
        $this->attributes['details'] = Crypt::encryptString(json_encode($value));
    }

    /**
     * Get the payment method details (decrypt after retrieving).
     */
    public function getDetailsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get masked card number for display.
     */
    public function getMaskedCardNumberAttribute(): string
    {
        if ($this->type !== 'credit_card' && $this->type !== 'debit_card') {
            return 'N/A';
        }

        $cardNumber = $this->details['card_number'] ?? '';
        if (strlen($cardNumber) > 4) {
            return '**** **** **** ' . substr($cardNumber, -4);
        }

        return '****';
    }

    /**
     * Get displayable expiry date.
     */
    public function getExpiryDateAttribute(): string
    {
        if ($this->type !== 'credit_card' && $this->type !== 'debit_card') {
            return 'N/A';
        }

        $month = str_pad($this->details['expiry_month'] ?? '', 2, '0', STR_PAD_LEFT);
        $year = $this->details['expiry_year'] ?? '';

        return $month . '/' . $year;
    }

    /**
     * Scope a query to only include default payment methods.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include payment methods of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Set as default payment method.
     */
    public function setAsDefault(): void
    {
        // Remove default from other payment methods of this user
        $this->user->paymentMethods()->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Check if this is the default payment method.
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Check if payment method is expired.
     */
    public function isExpired(): bool
    {
        if ($this->type !== 'credit_card' && $this->type !== 'debit_card') {
            return false;
        }

        $expiryMonth = $this->details['expiry_month'] ?? 0;
        $expiryYear = $this->details['expiry_year'] ?? 0;

        if ($expiryYear < date('Y')) {
            return true;
        }

        if ($expiryYear == date('Y') && $expiryMonth < date('m')) {
            return true;
        }

        return false;
    }
}
