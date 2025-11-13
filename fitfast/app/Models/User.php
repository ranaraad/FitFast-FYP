<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'measurements',
        'address',
        'shipping_address',
        'billing_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'measurements' => 'array',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the stores managed by the user (for store admins).
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'user_id');
    }

    // Add these methods to the User model
    public function chatSupportTickets(): HasMany
    {
        return $this->hasMany(ChatSupport::class, 'user_id');
    }

    public function assignedChats(): HasMany
    {
        return $this->hasMany(ChatSupport::class, 'admin_id');
    }

    /**
    * The items that belong to the user (many-to-many relationship)
    */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_user')
                    ->using(ItemUser::class)
                    ->withTimestamps();
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

     /**
     * Get the reviews written by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the cart for the user.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the payment methods for the user.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the payments for the user (through orders).
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Order::class);
    }

    /**
     * Get the default payment method for the user.
     */
    public function defaultPaymentMethod()
    {
        return $this->paymentMethods()->default()->first();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->isAdmin();
    }

    /**
     * Check if user is store admin.
     */
    public function isStoreAdmin(): bool
    {
        return $this->role && $this->role->isStoreAdmin();
    }

    /**
     * Check if user is regular user.
     */
    public function isUser(): bool
    {
        return $this->role && $this->role->isUser();
    }

    /**
     * Get managed stores for store admin (with fallback for other roles).
     */
    public function getManagedStores()
    {
        if ($this->isStoreAdmin()) {
            return $this->stores;
        }

        // For admins, return all stores
        if ($this->isAdmin()) {
            return Store::all();
        }

        // For regular users, return empty collection
        return collect();
    }
}
