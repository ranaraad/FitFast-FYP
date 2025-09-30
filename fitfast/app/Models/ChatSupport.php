<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSupport extends Model
{
    use HasFactory;

    protected $table = 'chat_support';

    protected $fillable = [
        'user_id',
        'admin_id',
        'message',
        'status',
        'type',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user that created the chat message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to the chat.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include in progress tickets.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include resolved tickets.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Check if the ticket is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if the ticket is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved' || $this->status === 'closed';
    }
}
