<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is admin.
     */
    public function isAdmin(): bool
    {
        return $this->name === 'Admin';
    }

    /**
     * Check if role is store admin.
     */
    public function isStoreAdmin(): bool
    {
        return $this->name === 'Store Admin';
    }

    /**
     * Check if role is regular user.
     */
    public function isUser(): bool
    {
        return $this->name === 'User';
    }
}
